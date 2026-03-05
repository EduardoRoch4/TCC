<?php
/**
 * Configurações gerais da aplicação
 */
session_start();

// Use '/' ao rodar com: php -S localhost:8097 -t public (na pasta do projeto)
define('BASE_URL', '/');
define('APP_NAME', 'InteliFood');

// Autoload simples para Models e Controllers
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/database.php';
