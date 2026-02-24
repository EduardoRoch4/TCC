<?php
/**
 * Logout - Encerra a sessão e redireciona
 */

// NÃO chame session_start() aqui novamente (já foi chamado em config/config.php)
// Isso evita o notice de "session already active"

require_once 'config/config.php';  // Isso inclui auth.php e define logout()

// Chama a função de logout (que destrói a sessão)
logout();

// Redireciona para a página inicial
header('Location: index.php');
exit;