<?php
$base=dirname(__DIR__);require $base.'/app/Config/App.php';require $base.'/app/Support/helpers.php';App\Config\App::loadEnv($base);
spl_autoload_register(function($c)use($base){if(str_starts_with($c,'App\\')){$f=$base.'/app/'.str_replace('\\','/',substr($c,4)).'.php';if(is_file($f))require$f;}});
$db=App\Config\Database::getInstance();$marker='phase5_'.bin2hex(random_bytes(6));$db->beginTransaction();
try{
 $db->prepare('INSERT INTO clientes(nome,cpf_cnpj) VALUES(:n,:d)')->execute([':n'=>'Teste temporario',':d'=>$marker]);$client=(int)$db->lastInsertId();
 $db->prepare('INSERT INTO aparelhos(cliente_id,marca,modelo) VALUES(:c,:m,:o)')->execute([':c'=>$client,':m'=>'Teste',':o'=>$marker]);$device=(int)$db->lastInsertId();
 $db->prepare("INSERT INTO ordens_servico(numero_os,cliente_id,aparelho_id,status,valor_mao_obra,valor_pecas,desconto) VALUES(:n,:c,:a,'Recebido',100.00,0,0)")->execute([':n'=>substr($marker,0,20),':c'=>$client,':a'=>$device]);$os=(int)$db->lastInsertId();
 $svc=new App\Services\ManualOsPaymentService($db);$p=$svc->register($os,4000,'Pix','Teste temporario',1);if($p['status']!=='parcial'||$p['remaining_cents']!==6000)throw new RuntimeException('parcial');
 $f=$svc->register($os,6000,'Dinheiro','Teste temporario',1);if($f['status']!=='pago'||$f['remaining_cents']!==0)throw new RuntimeException('integral');
 $counts=$db->query('SELECT (SELECT COUNT(*) FROM os_pagamentos WHERE os_id='.$os.') p,(SELECT COUNT(*) FROM financeiro WHERE os_id='.$os.") f,(SELECT COUNT(*) FROM os_historico WHERE os_id=".$os.') h')->fetch();if($counts['p']!=2||$counts['f']!=2||$counts['h']!=2)throw new RuntimeException('contagens');
 try{$svc->register($os,100,'Pix','duplicado',1);throw new RuntimeException('quitada aceita');}catch(App\Services\PaymentException $e){if($e->domainCode!=='ORDER_ALREADY_PAID')throw$e;}
 $db->rollBack();$check=$db->prepare('SELECT COUNT(*) FROM clientes WHERE cpf_cnpj=:d');$check->execute([':d'=>$marker]);if((int)$check->fetchColumn()!==0)throw new RuntimeException('rollback');echo "OsManualPaymentIntegrationTest: OK (fixture temporaria revertida)\n";
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();fwrite(STDERR,'Falha: '.$e->getMessage()."\n");exit(1);}
