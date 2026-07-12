<?php
$base=dirname(__DIR__);require $base.'/app/Config/App.php';require $base.'/app/Support/helpers.php';\App\Config\App::loadEnv($base);
spl_autoload_register(function($c)use($base){if(str_starts_with($c,'App\\')){$f=$base.'/app/'.str_replace('\\','/',substr($c,4)).'.php';if(is_file($f))require $f;}});
$db=\App\Config\Database::getInstance();$prefix='TESTE_MP_SECURITY_';$secret=$prefix.'SECRET_HTTP';$port=18765;$host='127.0.0.1';
putenv('MERCADO_PAGO_WEBHOOK_SECRET='.$secret);$_ENV['MERCADO_PAGO_WEBHOOK_SECRET']=$secret;
$cmd=[PHP_BINARY,'-S',$host.':'.$port,'-t',$base.'/public',$base.'/public/index.php'];$spec=[1=>['pipe','w'],2=>['pipe','w']];$proc=proc_open($cmd,$spec,$pipes,$base);
if(!is_resource($proc)){fwrite(STDERR,"Nao foi possivel iniciar servidor local\n");exit(1);}foreach($pipes as $pipe)stream_set_blocking($pipe,false);
$request=function($signature,$requestId,$body,$dataId)use($host,$port){$ch=curl_init('http://'.$host.':'.$port.'/index.php?url=mercadopago/pointWebhook&data_id='.rawurlencode($dataId));curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'POST',CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Signature: '.$signature,'X-Request-Id: '.$requestId],CURLOPT_TIMEOUT=>10]);$response=(string)curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);return [$status,$response];};
$fail=[];$ok=function($v,$m)use(&$fail){if(!$v)$fail[]=$m;};
try{
    $ready=false;for($i=0;$i<30;$i++){usleep(100000);$ch=curl_init('http://'.$host.':'.$port.'/index.php?url=login');curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>1]);curl_exec($ch);$ready=curl_getinfo($ch,CURLINFO_RESPONSE_CODE)>0;curl_close($ch);if($ready)break;}$ok($ready,'servidor local indisponivel');
    $id=$prefix.'EVENT';$requestId=$prefix.'REQUEST';$ts=(string)time();$body=json_encode(['type'=>'point','external_reference'=>$prefix.'UNKNOWN','data'=>['external_reference'=>$prefix.'UNKNOWN']],JSON_UNESCAPED_SLASHES);$manifest='id:'.strtolower($id).';request-id:'.$requestId.';ts:'.$ts.';';$sig='ts='.$ts.',v1='.hash_hmac('sha256',$manifest,$secret);
    [$status,$response]=$request($sig,$requestId,$body,$id);$ok($status===200&&str_contains($response,'"ok":true'),'webhook valido');
    [$status2]=$request($sig,$requestId,$body,$id);$ok($status2===200,'repeticao valida');
    $handles=[];$multi=curl_multi_init();for($i=0;$i<2;$i++){$ch=curl_init('http://'.$host.':'.$port.'/index.php?url=mercadopago/pointWebhook&data_id='.rawurlencode($id));curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'POST',CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Signature: '.$sig,'X-Request-Id: '.$requestId],CURLOPT_TIMEOUT=>10]);$handles[]=$ch;curl_multi_add_handle($multi,$ch);}do{$code=curl_multi_exec($multi,$active);if($active)curl_multi_select($multi,0.2);}while($active&&$code===CURLM_OK);foreach($handles as $ch){$ok((int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE)===200,'concorrencia webhook');curl_multi_remove_handle($multi,$ch);curl_close($ch);}curl_multi_close($multi);
    [$badStatus]=$request('ts='.$ts.',v1='.str_repeat('0',64),$requestId,$body,$id);$ok($badStatus===401,'assinatura invalida');
    [$missingStatus]=$request('','',$body,$id);$ok(in_array($missingStatus,[400,401],true),'headers ausentes');
    $finance=(int)$db->query("SELECT COUNT(*) FROM financeiro WHERE descricao LIKE ".$db->quote('%'.$prefix.'%'))->fetchColumn();$payments=(int)$db->query("SELECT COUNT(*) FROM os_pagamentos WHERE observacao LIKE ".$db->quote('%'.$prefix.'%'))->fetchColumn();$history=(int)$db->query("SELECT COUNT(*) FROM os_historico WHERE observacao LIKE ".$db->quote('%'.$prefix.'%'))->fetchColumn();$ok($finance+$payments+$history===0,'efeito financeiro antes/depois da validacao');
}finally{
    proc_terminate($proc);foreach($pipes as $pipe)fclose($pipe);proc_close($proc);$db->prepare('DELETE FROM mercado_pago_logs WHERE external_reference LIKE :p')->execute([':p'=>$prefix.'%']);putenv('MERCADO_PAGO_WEBHOOK_SECRET');unset($_ENV['MERCADO_PAGO_WEBHOOK_SECRET']);
}
$remaining=(int)$db->query("SELECT COUNT(*) FROM mercado_pago_logs WHERE external_reference LIKE ".$db->quote($prefix.'%'))->fetchColumn();$ok($remaining===0,'fixtures restantes');
if($fail){fwrite(STDERR,"MercadoPagoWebhookHttpTest falhou:\n- ".implode("\n- ",$fail).PHP_EOL);exit(1);}echo "MercadoPagoWebhookHttpTest: OK (rota real local, sem chamada externa)\n";
