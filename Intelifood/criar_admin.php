<?php
/**
 * Crie o usuário administrador (execute após importar sql/schema.sql)
 * Uso: php criar_admin.php
 */
require __DIR__ . '/config/config.php';

$email = 'admin@intelifood.com';
$senha = 'admin123';
$userModel = new Usuario();

if ($userModel->porEmail($email)) {
    echo "Admin já existe. Para alterar a senha, edite no banco ou remova o usuário e execute novamente.\n";
    exit(0);
}

$userModel->criar('Administrador', $email, $senha, 'admin');
echo "Administrador criado com sucesso!\n";
echo "Login: $email\n";
echo "Senha: $senha\n";
