<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit(1); }
$base=dirname(__DIR__);require $base.'/app/Config/App.php';require $base.'/app/Support/helpers.php';App\Config\App::loadEnv($base);
$opt=getopt('',['dry-run','apply','database:']);$apply=isset($opt['apply']);$dbName=$opt['database']??app_env('DB_DATABASE','');if(!preg_match('/^[A-Za-z0-9_-]+$/',(string)$dbName))exit(2);
$pdo=new PDO('mysql:host='.app_env('DB_HOST').';port='.app_env('DB_PORT').';dbname='.$dbName.';charset=utf8mb4',app_env('DB_USERNAME'),app_env('DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);$catalog=require $base.'/database/seeds/phone_models.php';
$exists=$pdo->prepare('SELECT COUNT(*) FROM aparelho_modelos WHERE LOWER(TRIM(marca))=:marca AND LOWER(TRIM(modelo))=:modelo');$insert=[];$present=0;
foreach($catalog as $brand=>$models)foreach($models as $model){$exists->execute([':marca'=>mb_strtolower(trim($brand),'UTF-8'),':modelo'=>mb_strtolower(trim($model),'UTF-8')]);if((int)$exists->fetchColumn()>0)$present++;else$insert[]=[$brand,$model];}
echo 'Catálogo: inserir='.count($insert).' existentes='.$present.PHP_EOL;if(!$apply){echo "DRY-RUN: nenhuma alteração realizada.\n";exit(0);}try{$pdo->beginTransaction();$stmt=$pdo->prepare('INSERT INTO aparelho_modelos(marca,modelo) VALUES(:marca,:modelo)');foreach($insert as[$b,$m])$stmt->execute([':marca'=>$b,':modelo'=>$m]);$pdo->commit();echo 'Aplicado: inseridos='.count($insert).PHP_EOL;}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,"Falha; transação revertida.\n");exit(3);}
