<?php

$sessionId = getenv('ROUTER_TEST_SESSION_ID') ?: '';
$userId = (int) (getenv('ROUTER_TEST_USER_ID') ?: 0);
$csrf = getenv('ROUTER_TEST_CSRF') ?: '';
if (!preg_match('/^[a-f0-9]{32}$/', $sessionId) || $userId < 1 || !preg_match('/^[a-f0-9]{64}$/', $csrf)) {
    exit(1);
}
session_id($sessionId);
session_name(getenv('ROUTER_TEST_COOKIE_NAME') ?: 'conectados_session');
session_start();
if (getenv('ROUTER_TEST_CLEANUP') === '1') {
    $_SESSION = [];
    session_destroy();
    exit;
}
$_SESSION['usuario_id'] = $userId;
$_SESSION['_csrf_token'] = $csrf;
$_SESSION['_last_activity'] = getenv('ROUTER_TEST_EXPIRED') === '1' ? time() - 86400 : time();
$_SESSION['_test_session'] = true;
if (getenv('ROUTER_TEST_PUBLIC_ACCOUNT') === '1') {
    $_SESSION['conta_publica_nome'] = 'Conta temporaria';
    $_SESSION['conta_publica_email'] = 'temporaria@example.invalid';
}
session_write_close();
