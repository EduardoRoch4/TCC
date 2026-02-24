<?php
/**
 * Configurações gerais - Copie para config.php e ajuste
 * NÃO commite config.php com credenciais reais
 */

session_start();

define('SITE_URL', 'http://localhost/Carteira');
define('SITE_NOME', 'CarteiraInvest');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/auth.php';
