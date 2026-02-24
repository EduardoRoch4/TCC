<?php
/**
 * Configurações gerais da aplicação
 */

session_start();

define('SITE_URL', 'http://localhost/Carteira');
define('SITE_NOME', 'CarteiraInvest');

// Inclui configuração do banco e autenticação
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/auth.php';
