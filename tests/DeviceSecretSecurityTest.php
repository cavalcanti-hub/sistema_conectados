<?php
$base=dirname(__DIR__);require $base.'/app/Config/App.php';require $base.'/app/Support/helpers.php';\App\Config\App::loadEnv($base);
spl_autoload_register(function($c)use($base){if(str_starts_with($c,'App\\')){$f=$base.'/app/'.str_replace('\\','/',substr($c,4)).'.php';if(is_file($f))require $f;}});
$fail=[];$ok=function($v,$m)use(&$fail){if(!$v)$fail[]=$m;};$prefix='TESTE_DEVICE_SECRET_';
$key=base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));$_ENV['DEVICE_SECRET_KEY']=$key;putenv('DEVICE_SECRET_KEY='.$key);$service=new \App\Services\DeviceSecretService();
foreach(['1234','1-2-3-6','Senha com acentos çã','TESTE_DEVICE_SECRET_'.str_repeat('L',180)] as $plain){$a=$service->encrypt($plain);$b=$service->encrypt($plain);$ok(str_starts_with($a,'v1:')&&$a!==$b&&$service->decrypt($a)===$plain,'roundtrip/nonce '.$plain[0]);}
$ok($service->encrypt('')===''&&$service->decrypt('')==='','valor vazio');
$cipher=$service->encrypt('TESTE_DEVICE_SECRET_PIN');$tampered=$cipher;$tampered[strlen($tampered)-2]=$tampered[strlen($tampered)-2]==='A'?'B':'A';try{$service->decrypt($tampered);$ok(false,'cipher adulterado aceito');}catch(\App\Services\DeviceSecretException){}
try{new \App\Services\DeviceSecretService('');$ok(false,'chave ausente aceita');}catch(\App\Services\DeviceSecretException){}
try{new \App\Services\DeviceSecretService(base64_encode('curta'));$ok(false,'chave invalida aceita');}catch(\App\Services\DeviceSecretException){}
try{(new \App\Services\DeviceSecretService(base64_encode(random_bytes(32))))->decrypt($cipher);$ok(false,'chave incorreta aceitou');}catch(\App\Services\DeviceSecretException){}

$db=\App\Config\Database::getInstance();$ids=[];
try{
 $db->prepare('INSERT INTO clientes(nome,cpf_cnpj) VALUES(:n,:d)')->execute([':n'=>$prefix.'CLIENTE',':d'=>'DS'.bin2hex(random_bytes(3))]);$ids['client']=(int)$db->lastInsertId();
 $model=new \App\Models\AparelhoModel();$ids['device']=(int)$model->create([':cliente_id'=>$ids['client'],':marca'=>'Teste',':modelo'=>'Teste',':imei'=>'',':cor'=>'',':senha_padrao'=>'9876',':estado_fisico'=>$prefix]);
 $stored=(string)$db->query('SELECT senha_padrao FROM aparelhos WHERE id='.$ids['device'])->fetchColumn();$ok($stored!=='9876'&&$service->decrypt($stored)==='9876','criacao criptografada');
 $model->updateFromOs($ids['device'],['marca'=>'Teste','modelo'=>'Teste','imei'=>'','cor'=>'','estado_fisico'=>$prefix],'',false);$preserved=(string)$db->query('SELECT senha_padrao FROM aparelhos WHERE id='.$ids['device'])->fetchColumn();$ok($preserved===$stored,'edicao vazia preserva');
 $model->updateFromOs($ids['device'],['marca'=>'Teste','modelo'=>'Teste','imei'=>'','cor'=>'','estado_fisico'=>$prefix],'1-4-7-8',false);$changed=(string)$db->query('SELECT senha_padrao FROM aparelhos WHERE id='.$ids['device'])->fetchColumn();$ok($changed!==$stored&&$service->decrypt($changed)==='1-4-7-8','edicao substitui');
 $wrong=new \App\Services\DeviceSecretService(base64_encode(random_bytes(32)));$_ENV['DEVICE_SECRET_KEY']=base64_encode(random_bytes(32));putenv('DEVICE_SECRET_KEY='.$_ENV['DEVICE_SECRET_KEY']);try{$model->updateFromOs($ids['device'],['marca'=>'Teste','modelo'=>'Teste','imei'=>'','cor'=>'','estado_fisico'=>$prefix],'••••••',false);}catch(Throwable){}$ok((string)$db->query('SELECT senha_padrao FROM aparelhos WHERE id='.$ids['device'])->fetchColumn()===$changed,'mascara/chave nao sobrescreve');
 $_ENV['DEVICE_SECRET_KEY']=$key;putenv('DEVICE_SECRET_KEY='.$key);$model->updateFromOs($ids['device'],['marca'=>'Teste','modelo'=>'Teste','imei'=>'','cor'=>'','estado_fisico'=>$prefix],'',true);$ok((string)$db->query('SELECT senha_padrao FROM aparelhos WHERE id='.$ids['device'])->fetchColumn()==='','remocao explicita');
}finally{if(!empty($ids['device']))$db->prepare('DELETE FROM aparelhos WHERE id=:id')->execute([':id'=>$ids['device']]);if(!empty($ids['client']))$db->prepare('DELETE FROM clientes WHERE id=:id')->execute([':id'=>$ids['client']]);}

$tmp='teste_device_secret_'.bin2hex(random_bytes(4));
try{$db->exec('CREATE DATABASE `'.$tmp.'` CHARACTER SET utf8mb4');$db->exec('CREATE TABLE `'.$tmp.'`.aparelhos(id INT AUTO_INCREMENT PRIMARY KEY,senha_padrao VARCHAR(512) NULL)');$legacy=['1111','2-5-8-0','texto teste'];$stmt=$db->prepare('INSERT INTO `'.$tmp.'`.aparelhos(senha_padrao) VALUES(:v)');foreach($legacy as $v)$stmt->execute([':v'=>$v]);$stmt->execute([':v'=>'']);$stmt->execute([':v'=>$service->encrypt('ja protegido')]);
 $run=function($args)use($base){$cmd=array_merge([PHP_BINARY,'-d','extension=sodium',$base.'/scripts/encrypt_existing_device_secrets.php'],$args);$p=proc_open($cmd,[1=>['pipe','w'],2=>['pipe','w']],$pipes,$base);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);foreach($pipes as $pipe)fclose($pipe);$code=proc_close($p);return [$code,json_decode(trim($out),true),$err];};
 [$code,$dry]=$run(['--dry-run','--database='.$tmp]);$ok($code===0&&$dry['alterados']===0&&$dry['legados']===3,'dry-run');$plainBefore=(int)$db->query("SELECT COUNT(*) FROM `$tmp`.aparelhos WHERE senha_padrao NOT LIKE 'v1:%' AND senha_padrao<>''")->fetchColumn();$ok($plainBefore===3,'dry-run alterou dados');
 [$code,$apply]=$run(['--apply','--confirm=CRIPTOGRAFAR','--database='.$tmp]);$ok($code===0&&$apply['alterados']===3,'apply isolado');$values=$db->query('SELECT senha_padrao FROM `'.$tmp.'`.aparelhos WHERE senha_padrao<>\'\' ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);$ok(count($values)===4&&$service->decrypt($values[0])==='1111'&&$service->decrypt($values[1])==='2-5-8-0'&&$service->decrypt($values[2])==='texto teste'&&$service->decrypt($values[3])==='ja protegido','sem perda');
 [$code,$again]=$run(['--apply','--confirm=CRIPTOGRAFAR','--database='.$tmp]);$ok($code===0&&$again['alterados']===0,'segundo apply');
}finally{$db->exec('DROP DATABASE IF EXISTS `'.$tmp.'`');}

$controller=file_get_contents($base.'/app/Controllers/OsController.php');$modelSource=file_get_contents($base.'/app/Models/AparelhoModel.php');$edit=file_get_contents($base.'/app/Views/os/edit.php');$view=file_get_contents($base.'/app/Views/os/view.php');
$ok(!str_contains($controller,"':senha_padrao' => \$_POST")&&!str_contains($controller,'senha_padrao=:senha_padrao'),'controller sem gravacao direta');$ok(str_contains($modelSource,'DeviceSecretService'),'model usa servico');$ok(str_contains($edit,'value=""')&&str_contains($edit,'Deixe em branco para manter'),'edicao nao preenche segredo');$ok(!str_contains($view,"\$os['senha_padrao'] ??")||str_contains($controller,'revealDeviceSecretForView'),'leitura controlada');
if(session_status()!==PHP_SESSION_ACTIVE)session_start();$ok(!str_contains(json_encode($_SESSION),$prefix),'sessao sem segredo');
putenv('DEVICE_SECRET_KEY');unset($_ENV['DEVICE_SECRET_KEY']);
if($fail){fwrite(STDERR,"DeviceSecretSecurityTest falhou:\n- ".implode("\n- ",$fail).PHP_EOL);exit(1);}echo "DeviceSecretSecurityTest: OK (crypto, gravacao e migracao isolada)\n";
