<?php
$base=dirname(__DIR__);require $base.'/app/Config/App.php';require $base.'/app/Support/helpers.php';
\App\Config\App::loadEnv($base);spl_autoload_register(function($c)use($base){if(str_starts_with($c,'App\\')){$f=$base.'/app/'.str_replace('\\','/',substr($c,4)).'.php';if(is_file($f))require $f;}});
$fail=[];$ok=function($v,$m)use(&$fail){if(!$v)$fail[]=$m;};

$calls=0;$captured=[];
$transport=function($method,$url,$body,$headers,$options)use(&$calls,&$captured){$calls++;$captured=compact('method','url','body','headers','options');return ['status'=>200,'body'=>'{"id":"TESTE_MP_SECURITY_OK"}','error'=>''];};
$client=new \App\Services\MercadoPagoHttpClient($transport);
$response=$client->request('PATCH','/point/integration-api/devices/TESTE_MP_SECURITY_DEVICE',['operating_mode'=>'STANDALONE'],'TESTE_MP_SECURITY_TOKEN');
$ok($response['id']==='TESTE_MP_SECURITY_OK','resposta valida');
$ok($captured['url']==='https://api.mercadopago.com/point/integration-api/devices/TESTE_MP_SECURITY_DEVICE','URL HTTPS');
$ok($captured['options']['verify_peer']===true&&$captured['options']['verify_peer_name']===true&&$captured['options']['verify_host']===2,'TLS e hostname ativos');
$ok($captured['options']['connect_timeout']===8&&$captured['options']['timeout']===22,'timeouts');
try{(new \App\Services\MercadoPagoHttpClient(function(){return ['status'=>0,'body'=>false,'error'=>'certificate verify failed'];}))->request('GET','/v1/orders/x',null,'token');$ok(false,'certificado invalido aceito');}catch(RuntimeException){}
try{(new \App\Services\MercadoPagoHttpClient(function(){return ['status'=>0,'body'=>false,'error'=>'hostname mismatch'];}))->request('GET','/v1/orders/x',null,'token');$ok(false,'hostname incorreto aceito');}catch(RuntimeException){}
try{(new \App\Services\MercadoPagoHttpClient(function(){return ['status'=>0,'body'=>false,'error'=>'timeout'];}))->request('GET','/v1/orders/x',null,'token');$ok(false,'timeout aceito');}catch(RuntimeException){}
foreach([401,500] as $status){try{(new \App\Services\MercadoPagoHttpClient(fn()=>['status'=>$status,'body'=>'{"error":"segredo-nao-deve-vazar"}','error'=>'']))->request('GET','/v1/orders/x',null,'token');$ok(false,'HTTP '.$status.' aceito');}catch(RuntimeException $e){$ok(!str_contains($e->getMessage(),'segredo-nao-deve-vazar'),'resposta sensivel no erro');}}
try{(new \App\Services\MercadoPagoHttpClient(fn()=>['status'=>200,'body'=>'invalido','error'=>'']))->request('GET','/v1/orders/x',null,'token');$ok(false,'JSON invalido aceito');}catch(RuntimeException){}
$before=$calls;try{$client->request('GET','/v1/orders/x',null,'');$ok(false,'token vazio aceito');}catch(RuntimeException){}$ok($calls===$before,'token vazio chamou transporte');

$secret='TESTE_MP_SECURITY_SECRET';$now=1700000000;$id='TESTE_MP_SECURITY_PAYMENT';$requestId='TESTE_MP_SECURITY_REQUEST';$raw=json_encode(['type'=>'payment','data'=>['id'=>$id]],JSON_UNESCAPED_SLASHES);$ts=(string)$now;
$manifest='id:'.strtolower($id).';request-id:'.$requestId.';ts:'.$ts.';';$signature='ts='.$ts.',v1='.hash_hmac('sha256',$manifest,$secret);
$validator=new \App\Services\MercadoPagoWebhookValidator($secret,300);$valid=$validator->validate($raw,$signature,$requestId,null,$now);$ok($valid['data_id']===$id,'assinatura valida');
$reject=function($name,$rawBody,$sig,$rid,$dataId,$time,$validator)use($ok){try{$validator->validate($rawBody,$sig,$rid,$dataId,$time);$ok(false,$name.' aceito');}catch(\App\Services\MercadoPagoWebhookException){}};
$reject('assinatura invalida',$raw,'ts='.$ts.',v1='.str_repeat('0',64),$requestId,null,$now,$validator);
$reject('assinatura ausente',$raw,'',$requestId,null,$now,$validator);
$reject('request id ausente',$raw,$signature,'',null,$now,$validator);
$reject('timestamp ausente',$raw,'v1='.str_repeat('0',64),$requestId,null,$now,$validator);
$reject('timestamp expirado',$raw,$signature,$requestId,null,$now+301,$validator);
$reject('payload invalido','{',$signature,$requestId,null,$now,$validator);
$reject('identificador ausente',json_encode(['type'=>'payment']),$signature,$requestId,null,$now,$validator);
$altered=json_encode(['type'=>'payment','data'=>['id'=>$id.'ALTERADO']]);$reject('corpo alterado',$altered,$signature,$requestId,null,$now,$validator);
try{(new \App\Services\MercadoPagoWebhookValidator('',300))->validate($raw,$signature,$requestId,null,$now);$ok(false,'segredo ausente aceito');}catch(\App\Services\MercadoPagoWebhookException){}
for($i=0;$i<2;$i++)$ok($validator->validate($raw,$signature,$requestId,null,$now)['data_id']===$id,'repeticao valida');

$httpSource=file_get_contents($base.'/app/Services/MercadoPagoHttpClient.php');$controllerSource=file_get_contents($base.'/app/Controllers/MercadoPagoController.php');$modelSource=file_get_contents($base.'/app/Models/MercadoPagoPointModel.php');
$ok(!str_contains($httpSource,'CURLOPT_SSL_VERIFYPEER=>false')&&!str_contains($controllerSource,'CURLOPT_SSL_VERIFYPEER, false'),'TLS nao desativado');
$ok(strpos($controllerSource,'->validate(')<strpos($controllerSource,'->processWebhook('),'assinatura antes do processamento');
$ok(!str_contains($controllerSource,"'payload' =>")&&!str_contains($controllerSource,'details'),'controller nao registra payload/resposta');
$ok(str_contains($modelSource,'FOR UPDATE')&&str_contains($modelSource,'financeiro_lancado_at'),'idempotencia transacional mantida');
$ok(!str_contains($modelSource,'Nao foi possivel consultar order:'),'webhook nao confia em payload quando consulta falha');
if($fail){fwrite(STDERR,"MercadoPagoSecurityTest falhou:\n- ".implode("\n- ",$fail).PHP_EOL);exit(1);}echo "MercadoPagoSecurityTest: OK (TLS/HMAC sem chamada externa)\n";
