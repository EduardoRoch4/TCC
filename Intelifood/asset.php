<?php
/**
 * Serve CSS/JS da pasta public (quando o servidor inicia na raiz do projeto).
 * Uso: asset.php?f=css/style.css
 */
$f = $_GET['f'] ?? '';
$f = str_replace(['../', '..\\'], '', $f);
if ($f === '' || strpos($f, '..') !== false) {
    http_response_code(400);
    exit;
}
$path = __DIR__ . '/public/' . $f;
$mimes = [
    'css' => 'text/css',
    'js'  => 'application/javascript',
    'ico' => 'image/x-icon',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
];
$ext = pathinfo($path, PATHINFO_EXTENSION);
if (!isset($mimes[$ext]) || !is_file($path)) {
    http_response_code(404);
    exit;
}
header('Content-Type: ' . $mimes[$ext] . '; charset=utf-8');
readfile($path);
