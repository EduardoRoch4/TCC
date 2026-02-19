<?php
/**
 * Autoloader para carregar classes automaticamente
 * Busca nas pastas: config, mvc/models, mvc/controllers, dao dentro de app/
 */
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../'; // app/
    $directories = [
        $baseDir . 'config/',
        $baseDir . 'mvc/models/',
        $baseDir . 'mvc/controllers/',
        $baseDir . 'dao/',
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

