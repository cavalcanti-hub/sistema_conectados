<?php
$host = $_SERVER['HTTP_HOST'] ?? 'conectadosassistencia.com.br';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $scheme . '://' . $host;
$catalogUrl = $baseUrl . '/index.php?url=vitrine%2Fcatalogo';
$imageUrl = $baseUrl . '/assets/img/share-catalogo-facebook.jpg';
$sharePageUrl = $baseUrl . ($_SERVER['REQUEST_URI'] ?? '/compartilhar-catalogo.php');
$title = 'Catalogo de Produtos - Conectados';
$description = 'Confira celulares, games, cabos, power banks e acessorios em destaque na Conectados.';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($catalogUrl, ENT_QUOTES) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Conectados">
    <meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description, ENT_QUOTES) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($sharePageUrl, ENT_QUOTES) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>">
    <meta property="og:image:url" content="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>">
    <meta property="og:image:secure_url" content="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Banner da loja virtual Conectados com produtos em destaque">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($title, ENT_QUOTES) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description, ENT_QUOTES) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #f5f8ff;
            color: #092347;
            text-align: center;
            padding: 24px;
        }
        a {
            color: #00349a;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main>
        <h1><?= htmlspecialchars($title) ?></h1>
        <p><?= htmlspecialchars($description) ?></p>
        <p><a href="<?= htmlspecialchars($catalogUrl, ENT_QUOTES) ?>">Abrir catalogo</a></p>
    </main>
    <script>
        window.setTimeout(function () {
            window.location.href = <?= json_encode($catalogUrl) ?>;
        }, 800);
    </script>
</body>
</html>
