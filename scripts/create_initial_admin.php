<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit(1); }
$base = dirname(__DIR__); require $base.'/app/Config/App.php'; require $base.'/app/Support/helpers.php'; App\Config\App::loadEnv($base);
$opt = getopt('', ['dry-run','apply','database:','name:','email:','password-stdin']);
$apply = isset($opt['apply']);
$dbName = $opt['database'] ?? app_env('DB_DATABASE','');
if (!preg_match('/^[A-Za-z0-9_-]+$/',(string)$dbName)) { fwrite(STDERR,"Banco invalido.\n"); exit(2); }
$pdo = new PDO('mysql:host='.app_env('DB_HOST').';port='.app_env('DB_PORT').';dbname='.$dbName.';charset=utf8mb4',app_env('DB_USERNAME'),app_env('DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
if ((int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil='Administrador'")->fetchColumn() > 0) { echo "Administrador já existente. Nenhuma alteração realizada.\n"; exit(0); }
$name=trim((string)($opt['name']??'')); $email=trim((string)($opt['email']??'')); $password=isset($opt['password-stdin'])?rtrim((string)fgets(STDIN),"\r\n"):'';
if (!$apply && $name==='' && $email==='') { echo "DRY-RUN: nenhum administrador existente; informe --name, --email e --password-stdin para validar a criação.\n"; exit(0); }
if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)) { fwrite(STDERR,"Nome ou e-mail inválido.\n"); exit(2); }
if (strlen($password)<12 || !preg_match('/[A-Z]/',$password) || !preg_match('/[a-z]/',$password) || !preg_match('/[0-9]/',$password) || !preg_match('/[^A-Za-z0-9]/',$password)) { fwrite(STDERR,"Senha fraca: use ao menos 12 caracteres, maiúscula, minúscula, número e símbolo.\n"); exit(2); }
$dup=$pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email=:email');$dup->execute([':email'=>$email]);if((int)$dup->fetchColumn()>0){fwrite(STDERR,"E-mail já cadastrado. Nenhuma alteração realizada.\n");exit(3);}
if(!$apply){echo "DRY-RUN: administrador válido seria criado; nenhuma alteração realizada.\n";exit(0);}
try{$pdo->beginTransaction();$stmt=$pdo->prepare("INSERT INTO usuarios(nome,email,senha,perfil,status) VALUES(:nome,:email,:senha,'Administrador','Ativo')");$stmt->execute([':nome'=>$name,':email'=>$email,':senha'=>password_hash($password,PASSWORD_DEFAULT)]);$pdo->commit();echo "Administrador inicial criado com sucesso.\n";}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,"Falha ao criar administrador; nenhuma alteração confirmada.\n");exit(4);}
