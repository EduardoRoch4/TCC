<?php
/**
 * Ponto de entrada quando o servidor inicia na raiz do projeto.
 * Redireciona para o front controller em public/
 */
$_GET['url'] = $_GET['url'] ?? 'menu/index';
require __DIR__ . '/public/index.php';
