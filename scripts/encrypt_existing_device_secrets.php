<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit(1);}
$base=dirname(__DIR__);require $base.'/app/Config/App.php';require $base.'/app/Support/helpers.php';\App\Config\App::loadEnv($base);
spl_autoload_register(function($c)use($base){if(str_starts_with($c,'App\\')){$f=$base.'/app/'.str_replace('\\','/',substr($c,4)).'.php';if(is_file($f))require $f;}});
$options=getopt('',['dry-run','apply','confirm:','limit:','database:']);$apply=isset($options['apply']);
if($apply&&($options['confirm']??'')!=='CRIPTOGRAFAR'){fwrite(STDERR,"Confirmacao obrigatoria: --apply --confirm=CRIPTOGRAFAR\n");exit(1);}
$limit=max(1,min(10000,(int)($options['limit']??500)));$db=\App\Config\Database::getInstance();
if(!empty($options['database'])){$name=(string)$options['database'];if(!preg_match('/^[A-Za-z0-9_-]{1,64}$/',$name)){fwrite(STDERR,"Banco invalido.\n");exit(1);}$db->exec('USE `'.str_replace('`','``',$name).'`');}
try{$service=new \App\Services\DeviceSecretService();$rows=$db->query('SELECT id,senha_padrao FROM aparelhos ORDER BY id LIMIT '.$limit)->fetchAll(PDO::FETCH_ASSOC);$counts=['vazios'=>0,'criptografados'=>0,'legados'=>0,'alterados'=>0];
    if($apply)$db->beginTransaction();
    foreach($rows as $row){$value=(string)($row['senha_padrao']??'');if($value===''){$counts['vazios']++;continue;}if($service->isEncrypted($value)){$service->decrypt($value);$counts['criptografados']++;continue;}$counts['legados']++;if($apply){$encrypted=$service->encrypt($value);$stmt=$db->prepare('UPDATE aparelhos SET senha_padrao=:secret WHERE id=:id AND senha_padrao=:legacy');$stmt->execute([':secret'=>$encrypted,':id'=>(int)$row['id'],':legacy'=>$value]);if($stmt->rowCount()!==1)throw new RuntimeException('Concorrencia detectada durante migracao.');$counts['alterados']++;}}
    if($apply)$db->commit();echo json_encode(['modo'=>$apply?'apply':'dry-run','processados'=>count($rows)]+$counts,JSON_UNESCAPED_SLASHES).PHP_EOL;
}catch(Throwable $e){if(isset($db)&&$db->inTransaction())$db->rollBack();fwrite(STDERR,"Migracao interrompida com seguranca.\n");exit(1);}
