<?php

$base = dirname(__DIR__);
require $base . '/app/Config/App.php';
require $base . '/app/Support/helpers.php';
\App\Config\App::loadEnv($base);
spl_autoload_register(function (string $class) use ($base): void {
    if (str_starts_with($class, 'App\\')) {
        $file = $base . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) require $file;
    }
});

$db = \App\Config\Database::getInstance();
$prefix = 'TESTE_PAGAMENTO_LEGADO_';
$run = bin2hex(random_bytes(5));
$baseUrl = 'http://localhost/sistema_conectados';
$cookieName = (string) app_env('SESSION_NAME', 'conectados_session');
$sessionId = bin2hex(random_bytes(16));
$csrf = bin2hex(random_bytes(32));
$failures = [];
$assert = static function (bool $ok, string $message) use (&$failures): void {
    if (!$ok) $failures[] = $message;
};

$request = static function (string $method, string $url, array $data = [], ?string $cookie = null): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_COOKIE => $cookie ?? '',
    ]);
    if ($method === 'POST') curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $raw = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);
    return ['status' => $status, 'headers' => substr($raw, 0, $headerSize), 'body' => substr($raw, $headerSize), 'error' => $error];
};

$extract = static function (string $html, string $name): string {
    return preg_match('/name=["\']' . preg_quote($name, '/') . '["\'][^>]*value=["\']([^"\']*)/i', $html, $m)
        ? html_entity_decode($m[1], ENT_QUOTES) : '';
};

$osByMarker = static function (string $marker) use ($db): array {
    $stmt = $db->prepare('SELECT * FROM ordens_servico WHERE problema_relatado = :marker ORDER BY id');
    $stmt->execute([':marker' => $marker]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
};

$counts = static function (int $osId) use ($db): array {
    $one = static function (string $sql) use ($db, $osId): int {
        $stmt = $db->prepare($sql); $stmt->execute([':id' => $osId]); return (int) $stmt->fetchColumn();
    };
    return [
        'payments' => $one('SELECT COUNT(*) FROM os_pagamentos WHERE os_id=:id'),
        'revenue' => $one("SELECT COUNT(*) FROM financeiro WHERE os_id=:id AND tipo='Receita' AND categoria='Pagamento OS'"),
        'history' => $one("SELECT COUNT(*) FROM os_historico WHERE os_id=:id AND observacao LIKE 'Pagamento manual de R$ %'"),
    ];
};

$createTokens = static function () use ($request, $extract, $baseUrl, $cookieName, $sessionId, $assert): array {
    $response = $request('GET', $baseUrl . '/os/create', [], $cookieName . '=' . $sessionId);
    $assert($response['status'] === 200, 'GET os/create deveria retornar 200; retornou ' . $response['status']);
    return [
        'csrf' => $extract($response['body'], '_csrf_token'),
        'operation' => $extract($response['body'], 'os_create_operation_id'),
        'nonce' => $extract($response['body'], 'os_create_payment_nonce'),
    ];
};

$postCreate = static function (string $marker, array $tokens, ?string $payment, string $method = 'Pix') use ($request, $baseUrl, $cookieName, $sessionId, &$clientId, $prefix, $run): array {
    $data = [
        '_csrf_token' => $tokens['csrf'], 'os_create_operation_id' => $tokens['operation'],
        'os_create_payment_nonce' => $tokens['nonce'], 'cliente_id' => $clientId,
        'marca' => $prefix . 'MARCA', 'modelo' => $prefix . 'MODELO_' . $run, 'imei' => '', 'cor' => '',
        'senha_padrao' => '', 'estado_fisico' => $prefix . 'ESTADO', 'problema_relatado' => $marker,
        'prioridade' => 'Normal', 'tecnico_id' => '', 'prazo_estimado' => '',
        'valor_mao_obra' => '100.00', 'valor_pecas' => '0.00',
        'pagamento_inicial_valor' => $payment ?? '', 'pagamento_inicial_forma' => $method,
        'pagamento_inicial_observacao' => $prefix . 'INICIAL',
    ];
    return $request('POST', $baseUrl . '/os/store', $data, $cookieName . '=' . $sessionId);
};

$cleanup = static function () use ($db, $prefix, $sessionId): void {
    $ids = $db->query("SELECT DISTINCT os.id FROM ordens_servico os LEFT JOIN aparelhos a ON a.id=os.aparelho_id LEFT JOIN clientes c ON c.id=os.cliente_id WHERE os.problema_relatado LIKE " . $db->quote($prefix . '%') . " OR a.estado_fisico LIKE " . $db->quote($prefix . '%') . " OR c.nome LIKE " . $db->quote($prefix . '%'))->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) {
        $db->prepare('DELETE FROM financeiro WHERE os_id=:id')->execute([':id' => $id]);
        $db->prepare('DELETE FROM os_pagamentos WHERE os_id=:id')->execute([':id' => $id]);
        $db->prepare('DELETE FROM os_historico WHERE os_id=:id')->execute([':id' => $id]);
    }
    if ($ids) $db->exec('DELETE FROM ordens_servico WHERE id IN (' . implode(',', array_map('intval', $ids)) . ')');
    $db->exec("DELETE FROM aparelhos WHERE estado_fisico LIKE " . $db->quote($prefix . '%'));
    $db->exec("DELETE FROM aparelho_modelos WHERE marca=" . $db->quote($prefix . 'MARCA') . " AND modelo LIKE " . $db->quote($prefix . '%'));
    $db->exec("DELETE FROM clientes WHERE nome LIKE " . $db->quote($prefix . '%'));
    $db->exec("DELETE FROM usuarios WHERE nome LIKE " . $db->quote($prefix . '%'));
    session_name((string) app_env('SESSION_NAME', 'conectados_session'));
    session_id($sessionId);
    @session_start(); $_SESSION = []; @session_destroy();
};

try {
    $db->prepare("INSERT INTO usuarios(nome,email,senha,perfil,status) VALUES(:n,:e,:s,'Administrador','Ativo')")
        ->execute([':n' => $prefix . $run . '_USUARIO', ':e' => strtolower($prefix . $run) . '@example.invalid', ':s' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT)]);
    $userId = (int) $db->lastInsertId();
    $db->prepare('INSERT INTO clientes(nome,cpf_cnpj) VALUES(:n,:d)')->execute([':n' => $prefix . $run . '_CLIENTE', ':d' => 'T' . $run]);
    $clientId = (int) $db->lastInsertId();

    session_name($cookieName); session_id($sessionId); session_start();
    $_SESSION['usuario_id'] = $userId; $_SESSION['perfil'] = 'Administrador'; $_SESSION['_csrf_token'] = $csrf;
    $_SESSION['_last_activity'] = time(); $_SESSION['_test_session'] = true; session_write_close();

    $marker = $prefix . $run . '_SEM';
    $response = $postCreate($marker, $createTokens(), null);
    $rows = $osByMarker($marker); $assert($response['status'] === 302 && count($rows) === 1, 'criacao sem pagamento');
    if ($rows) { $c = $counts((int) $rows[0]['id']); $assert($c === ['payments'=>0,'revenue'=>0,'history'=>0] && $rows[0]['situacao_pagamento'] === 'Pendente', 'criacao sem pagamento gerou financeiro'); }

    foreach ([['PARCIAL','40,00','Parcial',4000], ['INTEGRAL','100,00','Pago',10000]] as [$suffix,$amount,$status,$expectedCents]) {
        $marker = $prefix . $run . '_' . $suffix; $response = $postCreate($marker, $createTokens(), $amount);
        $rows = $osByMarker($marker); $assert($response['status'] === 302 && count($rows) === 1, 'criacao ' . $suffix);
        if ($rows) {
            $id=(int)$rows[0]['id']; $c=$counts($id);
            $paidCents=\App\Services\ManualOsPaymentService::decimalToCents((string)$db->query('SELECT COALESCE(SUM(valor),0) FROM os_pagamentos WHERE os_id='.$id)->fetchColumn());
            $assert($c === ['payments'=>1,'revenue'=>1,'history'=>1] && $paidCents===$expectedCents && $rows[0]['situacao_pagamento']===$status && $rows[0]['status']==='Recebido', 'contagens/status ' . $suffix);
        }
    }

    foreach ([['ZERO','0,00','Pix'],['NEG','-1,00','Pix'],['SCI','1e2','Pix'],['ACIMA','101,00','Pix'],['FORMA','40,00','Boleto']] as [$suffix,$amount,$method]) {
        $marker=$prefix.$run.'_INVALID_'.$suffix; $response=$postCreate($marker,$createTokens(),$amount,$method);
        $assert($response['status']===302 && count($osByMarker($marker))===0, 'invalido deixou OS: '.$suffix);
    }
    $tokens=$createTokens(); $tokens['csrf']='invalido'; $marker=$prefix.$run.'_INVALID_CSRF';
    $response=$postCreate($marker,$tokens,'40,00'); $assert($response['status']===403 && count($osByMarker($marker))===0,'CSRF invalido');
    $tokens=$createTokens(); $tokens['nonce']='invalido'; $marker=$prefix.$run.'_INVALID_NONCE';
    $postCreate($marker,$tokens,'40,00'); $assert(count($osByMarker($marker))===0,'nonce invalido deixou OS');

    $partial=$osByMarker($prefix.$run.'_PARCIAL')[0]; $id=(int)$partial['id'];
    foreach (['dashboard','clientes','os','financeiro','pdv','vitrine'] as $route) {
        $page=$request('GET',$baseUrl.'/'.$route,[],$cookieName.'='.$sessionId);
        $assert($page['status']===200,'regressao GET '.$route.' retornou '.$page['status']);
    }
    $assert($request('GET',$baseUrl.'/rota-inexistente',[],$cookieName.'='.$sessionId)['status']===404,'rota 404');
    $assert($request('GET',$baseUrl.'/os/store',[],$cookieName.'='.$sessionId)['status']===405,'rota 405');
    $assert($request('GET',$baseUrl.'/os/viewDetail?id='.$id,[],$cookieName.'='.$sessionId)['status']===200,'visualizacao OS');
    $assert($request('GET',$baseUrl.'/os/print?id='.$id,[],$cookieName.'='.$sessionId)['status']===200,'impressao OS');
    $edit=$request('GET',$baseUrl.'/os/edit?id='.$id,[],$cookieName.'='.$sessionId); $assert($edit['status']===200,'GET edicao');
    $editData=['_csrf_token'=>$extract($edit['body'],'_csrf_token'),'id'=>$id,'payment_nonce'=>$extract($edit['body'],'payment_nonce'),
        'marca'=>$prefix.'MARCA','modelo'=>$prefix.'MODELO_'.$run,'imei'=>'','cor'=>'','senha_padrao'=>'','estado_fisico'=>$prefix.'ESTADO',
        'status'=>'Recebido','prioridade'=>'Normal','tecnico_id'=>'','diagnostico_tecnico'=>'validado','servico_realizar'=>'','valor_mao_obra'=>'100.00','valor_pecas'=>'0.00','desconto'=>'0.00','prazo_estimado'=>'','forma_pagamento'=>'Pix','pagamento_valor'=>'','pagamento_forma'=>'Pix','pagamento_observacao'=>''];
    $request('POST',$baseUrl.'/os/update',$editData,$cookieName.'='.$sessionId); $request('POST',$baseUrl.'/os/update',$editData,$cookieName.'='.$sessionId);
    $assert($counts($id)===['payments'=>1,'revenue'=>1,'history'=>1],'edicao sem parcela duplicou');

    $edit=$request('GET',$baseUrl.'/os/edit?id='.$id,[],$cookieName.'='.$sessionId); $editData['_csrf_token']=$extract($edit['body'],'_csrf_token'); $editData['payment_nonce']=$extract($edit['body'],'payment_nonce'); $editData['pagamento_valor']='60,00';
    $newResponse=$request('POST',$baseUrl.'/os/update',$editData,$cookieName.'='.$sessionId); $repeatResponse=$request('POST',$baseUrl.'/os/update',$editData,$cookieName.'='.$sessionId);
    $newCounts=$counts($id);
    $assert($newCounts===['payments'=>2,'revenue'=>2,'history'=>2],'nova parcela/nonce repetido: '.json_encode($newCounts).' headers='.trim($newResponse['headers']).' repeat='.trim($repeatResponse['headers']));
    $current=$db->query('SELECT valor_total,situacao_pagamento,status FROM ordens_servico WHERE id='.$id)->fetch(PDO::FETCH_ASSOC);
    $assert($current['valor_total']==='100.00' && $current['situacao_pagamento']==='Pago' && $current['status']==='Recebido','estado apos nova parcela: '.json_encode($current));

    $edit=$request('GET',$baseUrl.'/os/edit?id='.$id,[],$cookieName.'='.$sessionId); $editData['_csrf_token']=$extract($edit['body'],'_csrf_token'); $editData['payment_nonce']=$extract($edit['body'],'payment_nonce'); $editData['pagamento_valor']=''; $editData['valor_mao_obra']='80.00';
    $request('POST',$baseUrl.'/os/update',$editData,$cookieName.'='.$sessionId);
    $after=$db->query('SELECT valor_total FROM ordens_servico WHERE id='.$id)->fetchColumn(); $afterCounts=$counts($id); $assert((string)$after==='100.00' && $afterCounts===['payments'=>2,'revenue'=>2,'history'=>2],'reducao do total nao fez rollback: total='.$after.' counts='.json_encode($afterCounts));

    $tokens=$createTokens(); $marker=$prefix.$run.'_DUP_NONCE';
    $payloadBuilder=static function()use($clientId,$prefix,$run,$marker,$tokens){return ['_csrf_token'=>$tokens['csrf'],'os_create_operation_id'=>$tokens['operation'],'os_create_payment_nonce'=>$tokens['nonce'],'cliente_id'=>$clientId,'marca'=>$prefix.'MARCA','modelo'=>$prefix.'MODELO_'.$run,'imei'=>'','cor'=>'','senha_padrao'=>'','estado_fisico'=>$prefix.'ESTADO','problema_relatado'=>$marker,'prioridade'=>'Normal','tecnico_id'=>'','prazo_estimado'=>'','valor_mao_obra'=>'100.00','valor_pecas'=>'0.00','pagamento_inicial_valor'=>'40,00','pagamento_inicial_forma'=>'Pix','pagamento_inicial_observacao'=>$prefix.'CONCORRENTE'];};
    $payload=http_build_query($payloadBuilder()); $cmd=['curl.exe','-sS','-o','NUL','-w','%{http_code}','-X','POST','-H','Cookie: '.$cookieName.'='.$sessionId,'--data',$payload,$baseUrl.'/os/store'];
    $spec=[1=>['pipe','w'],2=>['pipe','w']]; $p1=proc_open($cmd,$spec,$pipes1); $p2=proc_open($cmd,$spec,$pipes2);
    foreach ([[$p1,$pipes1],[$p2,$pipes2]] as [$p,$pipes]) { stream_get_contents($pipes[1]); stream_get_contents($pipes[2]); foreach($pipes as $pipe)fclose($pipe); proc_close($p); }
    $rows=$osByMarker($marker); $assert(count($rows)===1 && $counts((int)$rows[0]['id'])===['payments'=>1,'revenue'=>1,'history'=>1],'concorrencia criacao mesmo nonce');

    $tokensA=$createTokens(); $tokensB=$createTokens(); $markerA=$prefix.$run.'_ABAS_A'; $markerB=$prefix.$run.'_ABAS_B';
    $makePayload=static function(string $m,array $t)use($clientId,$prefix,$run){return http_build_query(['_csrf_token'=>$t['csrf'],'os_create_operation_id'=>$t['operation'],'os_create_payment_nonce'=>$t['nonce'],'cliente_id'=>$clientId,'marca'=>$prefix.'MARCA','modelo'=>$prefix.'MODELO_'.$run,'imei'=>'','cor'=>'','senha_padrao'=>'','estado_fisico'=>$prefix.'ESTADO','problema_relatado'=>$m,'prioridade'=>'Normal','tecnico_id'=>'','prazo_estimado'=>'','valor_mao_obra'=>'100.00','valor_pecas'=>'0.00','pagamento_inicial_valor'=>'40,00','pagamento_inicial_forma'=>'Pix','pagamento_inicial_observacao'=>$prefix.'ABAS']);};
    $cmdA=['curl.exe','-sS','-o','NUL','-X','POST','-H','Cookie: '.$cookieName.'='.$sessionId,'--data',$makePayload($markerA,$tokensA),$baseUrl.'/os/store'];
    $cmdB=['curl.exe','-sS','-o','NUL','-X','POST','-H','Cookie: '.$cookieName.'='.$sessionId,'--data',$makePayload($markerB,$tokensB),$baseUrl.'/os/store'];
    $pa=proc_open($cmdA,$spec,$pipesA); $pb=proc_open($cmdB,$spec,$pipesB);
    foreach ([[$pa,$pipesA],[$pb,$pipesB]] as [$p,$pipes]) { stream_get_contents($pipes[1]); stream_get_contents($pipes[2]); foreach($pipes as $pipe)fclose($pipe); proc_close($p); }
    foreach([$markerA,$markerB] as $m){$rows=$osByMarker($m);$assert(count($rows)===1 && $counts((int)$rows[0]['id'])===['payments'=>1,'revenue'=>1,'history'=>1],'nonce distinto '.$m);}
} catch (Throwable $e) {
    $failures[] = get_class($e) . ': ' . $e->getMessage();
} finally {
    $cleanup();
}

$remaining=(int)$db->query("SELECT (SELECT COUNT(*) FROM usuarios WHERE nome LIKE ".$db->quote($prefix.'%').")+(SELECT COUNT(*) FROM clientes WHERE nome LIKE ".$db->quote($prefix.'%').")+(SELECT COUNT(*) FROM aparelhos WHERE estado_fisico LIKE ".$db->quote($prefix.'%').")+(SELECT COUNT(*) FROM ordens_servico WHERE problema_relatado LIKE ".$db->quote($prefix.'%').")")->fetchColumn();
$assert($remaining===0,'fixtures restantes: '.$remaining);
if($failures){fwrite(STDERR,"OsLegacyPaymentHttpTest falhou:\n- ".implode("\n- ",$failures).PHP_EOL);exit(1);}
echo 'OsLegacyPaymentHttpTest: OK (HTTP autenticado, concorrencia de criacao e limpeza)' . PHP_EOL;
