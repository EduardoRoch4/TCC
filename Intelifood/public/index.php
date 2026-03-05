<?php
/**
 * Front Controller - InteliFood
 */
require_once dirname(__DIR__) . '/config/config.php';

$url = $_GET['url'] ?? 'menu/index';
$url = rtrim($url, '/');
$partes = explode('/', $url);
$controllerName = ucfirst($partes[0] ?? 'menu') . 'Controller';
$action = $partes[1] ?? 'index';

if (!class_exists($controllerName)) {
    $controllerName = 'MenuController';
    $action = 'index';
}

$controller = new $controllerName();
if (!method_exists($controller, $action)) {
    $action = 'index';
}

$controller->$action();
