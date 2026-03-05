<?php
/**
 * Roteador para o servidor embutido do PHP.
 * Use: php -S localhost:8097 router.php (na pasta Intelifood)
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$arquivo = __DIR__ . '/public' . $uri;

// Scripts PHP (ex.: asset.php) devem ser executados, não servidos como arquivo
if (preg_match('/\.php$/i', $uri) && file_exists($arquivo) && is_file($arquivo)) {
    require $arquivo;
    return true;
}

// Servir arquivos estáticos de public/ (css, js, etc.)
if ($uri !== '/' && $uri !== '' && file_exists($arquivo) && is_file($arquivo)) {
    $mimes = [
        'css' => 'text/css',
        'js'  => 'application/javascript',
        'ico' => 'image/x-icon',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
    ];
    $ext = pathinfo($arquivo, PATHINFO_EXTENSION);
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    readfile($arquivo);
    return true;
}

// Garantir que a URL padrão seja o cardápio
if ($uri === '/' || $uri === '') {
    $_GET['url'] = $_GET['url'] ?? 'menu/index';
}

require __DIR__ . '/public/index.php';
