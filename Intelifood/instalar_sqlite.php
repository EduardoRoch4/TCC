<?php
/**
 * Cria o banco SQLite e as tabelas. Execute uma vez: php instalar_sqlite.php
 * Use quando DB_DRIVER = 'sqlite' em config/database.php (não precisa de MySQL).
 */
$dir = __DIR__ . '/data';
$arquivo = $dir . '/intelifood.sqlite';
$schema = __DIR__ . '/sql/schema-sqlite.sql';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

if (!file_exists($schema)) {
    fwrite(STDERR, "Arquivo não encontrado: $schema\n");
    exit(1);
}

$sql = file_get_contents($schema);
$pdo = new PDO('sqlite:' . $arquivo, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec($sql);

echo "Banco SQLite criado: $arquivo\n";
echo "Tabelas e dados iniciais instalados.\n";
echo "\nAgora execute: php criar_admin.php\n";
echo "Depois acesse o site e faça login com admin@intelifood.com / admin123\n";
