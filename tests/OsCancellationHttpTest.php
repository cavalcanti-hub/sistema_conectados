<?php
$base=dirname(__DIR__);
require $base.'/app/Config/App.php'; require $base.'/app/Support/helpers.php';
\App\Config\App::loadEnv($base);
spl_autoload_register(function($c)use($base){if(str_starts_with($c,'App\\')){$f=$base.'/app/'.str_replace('\\','/',substr($c,4)).'.php';if(is_file($f))require $f;}});
$db=\App\Config\Database::getInstance(); $prefix='TESTE_CANCELAMENTO_OS_'; $run=bin2hex(random_bytes(4));
$baseUrl='http://localhost/sistema_conectados'; $cookieName=(string)app_env('SESSION_NAME','conectados_session'); $fail=[]; $ids=[];
$ok=function($v,$m)use(&$fail){if(!$v)$fail[]=$m;};
$request=function($method,$url,$data=[],$cookie=''){ $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_COOKIE=>$cookie,CURLOPT_POSTFIELDS=>$method==='POST'?http_build_query($data):null,CURLOPT_TIMEOUT=>20]);$raw=(string)curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$hs=(int)curl_getinfo($ch,CURLINFO_HEADER_SIZE);curl_close($ch);return ['status'=>$status,'headers'=>substr($raw,0,$hs),'body'=>substr($raw,$hs)];};
$makeSession=function($userId,$profile)use($cookieName){$sid=bin2hex(random_bytes(16));$csrf=bin2hex(random_bytes(32));session_name($cookieName);session_id($sid);session_start();$_SESSION=['usuario_id'=>$userId,'perfil'=>$profile,'_csrf_token'=>$csrf,'_last_activity'=>time(),'_test_session'=>true];session_write_close();return ['id'=>$sid,'csrf'=>$csrf];};
$createOs=function($suffix)use($db,$prefix,$run,&$ids){
    $db->prepare('INSERT INTO aparelhos(cliente_id,marca,modelo,estado_fisico,cor) VALUES(:c,:m,:o,:e,:cor)')->execute([':c'=>$ids['client'],':m'=>$prefix.'MARCA',':o'=>$prefix.$suffix,':e'=>"Checklist de Entrada:\n- Tela: OK\n\n".$prefix.'ESTADO',':cor'=>'Preto']);$device=(int)$db->lastInsertId();$ids['devices'][]=$device;
    $number='TC'.substr($run.$suffix,0,18);$db->prepare("INSERT INTO ordens_servico(numero_os,cliente_id,aparelho_id,status,problema_relatado,valor_mao_obra,valor_pecas,fotos) VALUES(:n,:c,:a,'Recebido',:p,100,0,:f)")->execute([':n'=>$number,':c'=>$ids['client'],':a'=>$device,':p'=>$prefix.$run.$suffix,':f'=>'["'.$prefix.'foto.jpg"]']);$id=(int)$db->lastInsertId();$ids['os'][]=$id;
    $db->prepare("INSERT INTO os_historico(os_id,usuario_id,status_anterior,status_novo,observacao) VALUES(:o,:u,'','Recebido',:x)")->execute([':o'=>$id,':u'=>$ids['admin'],':x'=>$prefix.'HISTORICO']);return $id;
};
$cancel=function($os,$session,$reason='Motivo de teste')use($request,$baseUrl,$cookieName){return $request('POST',$baseUrl.'/os/cancel',['_csrf_token'=>$session['csrf'],'id'=>$os,'motivo'=>$reason],$cookieName.'='.$session['id']);};
$state=function($os)use($db){$s=$db->prepare('SELECT status,cliente_id,aparelho_id,fotos FROM ordens_servico WHERE id=:id');$s->execute([':id'=>$os]);$row=$s->fetch(PDO::FETCH_ASSOC);$h=$db->prepare("SELECT COUNT(*) FROM os_historico WHERE os_id=:id AND status_novo='Cancelado'");$h->execute([':id'=>$os]);$row['cancel_history']=(int)$h->fetchColumn();return $row;};
$cleanup=function()use($db,$prefix,&$ids,$cookieName){
    foreach(array_reverse($ids['os']??[]) as $id){foreach(['mercado_pago_point_orders','estoque_movimentacoes','pdv_vendas','financeiro','os_pagamentos','os_historico'] as $t)$db->prepare("DELETE FROM $t WHERE os_id=:id")->execute([':id'=>$id]);$db->prepare('DELETE FROM auditoria_logs WHERE entidade=:e AND entidade_id=:id')->execute([':e'=>'os',':id'=>$id]);$db->prepare('DELETE FROM ordens_servico WHERE id=:id')->execute([':id'=>$id]);}
    foreach(array_reverse($ids['devices']??[]) as $id)$db->prepare('DELETE FROM aparelhos WHERE id=:id')->execute([':id'=>$id]);
    if(!empty($ids['product']))$db->prepare('DELETE FROM estoque WHERE id=:id')->execute([':id'=>$ids['product']]);
    if(!empty($ids['client']))$db->prepare('DELETE FROM clientes WHERE id=:id')->execute([':id'=>$ids['client']]);
    foreach(['admin','attendant'] as $k)if(!empty($ids[$k]))$db->prepare('DELETE FROM usuarios WHERE id=:id')->execute([':id'=>$ids[$k]]);
};
try{
    foreach([['admin','Administrador'],['attendant','Atendente']] as [$key,$profile]){$db->prepare("INSERT INTO usuarios(nome,email,senha,perfil,status) VALUES(:n,:e,:s,:p,'Ativo')")->execute([':n'=>$prefix.$run.$key,':e'=>strtolower($prefix.$run.$key).'@example.invalid',':s'=>password_hash(bin2hex(random_bytes(12)),PASSWORD_BCRYPT),':p'=>$profile]);$ids[$key]=(int)$db->lastInsertId();}
    $db->prepare('INSERT INTO clientes(nome,cpf_cnpj) VALUES(:n,:d)')->execute([':n'=>$prefix.$run.'CLIENTE',':d'=>'C'.$run]);$ids['client']=(int)$db->lastInsertId();
    $admin=$makeSession($ids['admin'],'Administrador');$attendant=$makeSession($ids['attendant'],'Atendente');

    $simple=$createOs('SIMPLE');$before=$state($simple);$response=$cancel($simple,$admin,'Equipamento nao sera reparado');$after=$state($simple);
    $ok($response['status']===302 && $after['status']==='Cancelado' && $after['cancel_history']===1,'cancelar OS simples');
    $ok($before['cliente_id']===$after['cliente_id']&&$before['aparelho_id']===$after['aparelho_id']&&$before['fotos']===$after['fotos'],'relacionamentos/fotos preservados');
    $h=$db->prepare('SELECT COUNT(*) FROM os_historico WHERE os_id=:id');$h->execute([':id'=>$simple]);$ok((int)$h->fetchColumn()===2,'historico anterior preservado');
    $cancel($simple,$admin,'Repeticao');$ok($state($simple)['cancel_history']===1,'idempotencia');

    $empty=$createOs('EMPTY');$cancel($empty,$admin,'');$ok($state($empty)['status']==='Recebido','motivo vazio');
    $csrfOs=$createOs('CSRF');$bad=$admin;$bad['csrf']='invalido';$response=$cancel($csrfOs,$bad);$ok($response['status']===403&&$state($csrfOs)['status']==='Recebido','CSRF invalido');
    $forbidden=$createOs('FORBID');$response=$cancel($forbidden,$attendant);$ok($response['status']===403&&$state($forbidden)['status']==='Recebido','usuario sem permissao');
    $response=$cancel(2147483646,$admin);$ok($response['status']===302,'OS inexistente segura');

    $payment=$createOs('PAY');$db->prepare("INSERT INTO os_pagamentos(os_id,valor,forma_pagamento,data_pagamento,usuario_id) VALUES(:o,10,'Pix',CURDATE(),:u)")->execute([':o'=>$payment,':u'=>$ids['admin']]);$db->prepare("INSERT INTO financeiro(tipo,categoria,descricao,valor,os_id,usuario_id) VALUES('Receita','Pagamento OS',:d,10,:o,:u)")->execute([':d'=>$prefix,':o'=>$payment,':u'=>$ids['admin']]);$cancel($payment,$admin);$preserved=(int)$db->query('SELECT (SELECT COUNT(*) FROM os_pagamentos WHERE os_id='.$payment.')+(SELECT COUNT(*) FROM financeiro WHERE os_id='.$payment.')')->fetchColumn();$ok($state($payment)['status']==='Recebido'&&$preserved===2,'OS com pagamento/financeiro');
    $db->prepare("INSERT INTO estoque(nome,tipo,categoria,quantidade,custo,preco_venda) VALUES(:n,'peca','Teste',1,1,1)")->execute([':n'=>$prefix.$run]);$ids['product']=(int)$db->lastInsertId();
    $stock=$createOs('STOCK');$db->prepare("INSERT INTO estoque_movimentacoes(produto_id,tipo,quantidade,motivo,os_id,usuario_id) VALUES(:p,'saida',1,:m,:o,:u)")->execute([':p'=>$ids['product'],':m'=>$prefix,':o'=>$stock,':u'=>$ids['admin']]);$cancel($stock,$admin);$ok($state($stock)['status']==='Recebido','OS com estoque');
    $sale=$createOs('SALE');$db->prepare("INSERT INTO pdv_vendas(numero_venda,cliente_id,os_id,usuario_id,status) VALUES(:n,:c,:o,:u,'aberta')")->execute([':n'=>'TV'.$run,':c'=>$ids['client'],':o'=>$sale,':u'=>$ids['admin']]);$cancel($sale,$admin);$ok($state($sale)['status']==='Recebido','OS com venda');
    $point=$createOs('POINT');$db->prepare("INSERT INTO mercado_pago_point_orders(os_id,numero_os,external_reference,status,amount) VALUES(:o,:n,:e,'created',100)")->execute([':o'=>$point,':n'=>'TP'.$run,':e'=>$prefix.$run]);$cancel($point,$admin);$ok($state($point)['status']==='Recebido','OS com Point');

    $rollback=$createOs('ROLLBACK');$service=new class($db) extends \App\Services\OsCancellationService{protected function insertHistory(int $osId,int $userId,string $previousStatus,string $reason):void{throw new RuntimeException('falha controlada');}};
    try{$service->cancel($rollback,$ids['admin'],'Falha controlada');$ok(false,'falha controlada nao lancou');}catch(RuntimeException $e){}
    $ok($state($rollback)['status']==='Recebido'&&$state($rollback)['cancel_history']===0,'rollback integral');

    $cancelledEdit=$request('POST',$baseUrl.'/os/update',['_csrf_token'=>$admin['csrf'],'id'=>$simple,'status'=>'Entregue'],$cookieName.'='.$admin['id']);$ok($cancelledEdit['status']===302&&$state($simple)['status']==='Cancelado','OS cancelada somente leitura');
    $wrongMethod=$request('GET',$baseUrl.'/os/cancel',[],$cookieName.'='.$admin['id']);$ok($wrongMethod['status']===405,'cancelamento deve aceitar somente POST');
    $view=$request('GET',$baseUrl.'/os/viewDetail?id='.$simple,[],$cookieName.'='.$admin['id']);$print=$request('GET',$baseUrl.'/os/print?id='.$simple,[],$cookieName.'='.$admin['id']);$ok($view['status']===200&&str_contains($view['body'],'CANCELADA')&&$print['status']===200&&str_contains($print['body'],'CANCELADA'),'consulta/impressao cancelada: view='.$view['status'].'/'.(str_contains($view['body'],'CANCELADA')?'sim':'nao').' print='.$print['status'].'/'.(str_contains($print['body'],'CANCELADA')?'sim':'nao'));
}catch(Throwable $e){$fail[]=get_class($e).': '.$e->getMessage();}finally{$cleanup();}
$remaining=0;foreach(['usuarios'=>'nome','clientes'=>'nome','aparelhos'=>'estado_fisico','ordens_servico'=>'problema_relatado','estoque'=>'nome'] as $t=>$c){$remaining+=(int)$db->query("SELECT COUNT(*) FROM $t WHERE $c LIKE ".$db->quote($prefix.'%'))->fetchColumn();}$ok($remaining===0,'fixtures restantes: '.$remaining);
if($fail){fwrite(STDERR,"OsCancellationHttpTest falhou:\n- ".implode("\n- ",$fail).PHP_EOL);exit(1);}echo "OsCancellationHttpTest: OK (HTTP, bloqueios, rollback e limpeza)\n";
