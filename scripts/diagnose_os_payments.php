<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$base=dirname(__DIR__); require $base.'/app/Config/App.php'; require $base.'/app/Support/helpers.php';
App\Config\App::loadEnv($base);
spl_autoload_register(function($class)use($base){if(str_starts_with($class,'App\\')){$f=$base.'/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($f))require $f;}});
$db=App\Config\Database::getInstance();
$checks=[
'total_pago_negativo'=>"SELECT os_id AS id, SUM(valor) AS valor FROM os_pagamentos GROUP BY os_id HAVING SUM(valor)<0",
'pago_acima_total'=>"SELECT o.id, SUM(p.valor)-o.valor_total AS valor FROM ordens_servico o JOIN os_pagamentos p ON p.os_id=o.id GROUP BY o.id,o.valor_total HAVING SUM(p.valor)>o.valor_total",
'status_pago_com_saldo'=>"SELECT o.id, o.valor_total-COALESCE(SUM(p.valor),0) AS valor FROM ordens_servico o LEFT JOIN os_pagamentos p ON p.os_id=o.id WHERE o.situacao_pagamento='Pago' GROUP BY o.id,o.valor_total HAVING valor>0",
'status_pendente_sem_saldo'=>"SELECT o.id, o.valor_total-COALESCE(SUM(p.valor),0) AS valor FROM ordens_servico o LEFT JOIN os_pagamentos p ON p.os_id=o.id WHERE o.situacao_pagamento='Pendente' GROUP BY o.id,o.valor_total HAVING valor<=0 AND o.valor_total>0",
'financeiro_os_sem_pagamento'=>"SELECT f.os_id AS id, SUM(f.valor) AS valor FROM financeiro f LEFT JOIN os_pagamentos p ON p.os_id=f.os_id WHERE f.categoria='Pagamento OS' AND f.os_id IS NOT NULL AND p.id IS NULL GROUP BY f.os_id",
];
foreach($checks as $name=>$sql){$rows=$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);echo $name.': '.count($rows).PHP_EOL;foreach($rows as $row)echo '  OS_ID='.(int)$row['id'].' VALOR='.(string)$row['valor'].PHP_EOL;}
