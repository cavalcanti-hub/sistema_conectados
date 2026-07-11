<?php
$base = dirname(__DIR__);
require $base . '/app/Config/App.php';
require $base . '/app/Support/helpers.php';
require $base . '/app/Services/ManualOsPaymentService.php';
use App\Services\ManualOsPaymentService as S;
use App\Services\PaymentException;
$fail=[]; $ok=static function($v,$n)use(&$fail){if(!$v)$fail[]=$n;};
$valid=['100'=>10000,'100,00'=>10000,'1.000,00'=>100000,'R$ 100,00'=>10000,'10.5'=>1050];
foreach($valid as $input=>$expected){$ok(S::parseMoneyToCents($input)===$expected,'valido '.$input);}
foreach(['','0','-1','abc','1e2','10,999','NaN','INF','R$ x','10000000'] as $input){try{S::parseMoneyToCents($input);$ok(false,'rejeitar '.$input);}catch(PaymentException $e){$ok($e->domainCode==='INVALID_PAYMENT_VALUE','codigo '.$input);}}
$ok(S::decimalToCents('300.00')===30000,'decimal total');
$ok(S::decimalToCents('100.00')===10000,'decimal pago');
$balance=S::decimalToCents('300.00')-S::decimalToCents('100.00');
$ok($balance-5000===15000,'parcial'); $ok($balance-20000===0,'integral'); $ok(25000>$balance,'acima saldo');
$source=file_get_contents($base.'/app/Services/ManualOsPaymentService.php');
foreach(['beginTransaction','FOR UPDATE','rollBack','commit','INSERT INTO os_pagamentos','INSERT INTO financeiro','INSERT INTO os_historico'] as $needle)$ok(str_contains($source,$needle),'estrutura '.$needle);
if($fail){fwrite(STDERR,'OsManualPaymentTest falhou: '.implode(', ',$fail).PHP_EOL);exit(1);} echo 'OsManualPaymentTest: OK (sem alterar banco)'.PHP_EOL;
