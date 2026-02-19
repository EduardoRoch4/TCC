<?php
// Desabilitar exibição de erros ANTES de qualquer output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar output buffering IMEDIATAMENTE
if (!ob_get_level()) {
    ob_start();
}

// Função para retornar JSON de forma segura
function sendJsonResponse($data) {
    // Limpar qualquer output anterior
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Tratamento de erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        // Incluir detalhes do erro para debug (remover em produção)
        $errorMsg = 'Erro fatal no servidor';
        if (isset($error['message'])) {
            $errorMsg .= ': ' . $error['message'];
            if (isset($error['file'])) {
                $errorMsg .= ' em ' . basename($error['file']) . ':' . $error['line'];
            }
        }
        echo json_encode(['status' => 'error', 'message' => $errorMsg]);
        exit;
    }
});

// Iniciar sessão silenciosamente
try {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
} catch (Exception $e) {
    sendJsonResponse(['status' => 'error', 'message' => 'Erro ao iniciar sessão']);
}

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    sendJsonResponse(['status' => 'error', 'message' => 'Usuário não autenticado']);
}

// Carregar autoloader e classes necessárias
require_once __DIR__ . '/../app/config/autoload.php';

$pagamentoController = new PagamentoController();
$id_usuario = $_SESSION['id_usuario'];

try {
    $input = file_get_contents('php://input');
    $data = null;

    // Tentar decodificar JSON apenas se houver input
    if (!empty($input)) {
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['status' => 'error', 'message' => 'Erro ao decodificar JSON: ' . json_last_error_msg()]);
        }
    }

    // Se não houver dados após decodificação, retornar erro
    if ($data === null || !is_array($data)) {
        sendJsonResponse(['status' => 'error', 'message' => 'Nenhum dado válido recebido']);
    }
} catch (Exception $e) {
    sendJsonResponse(['status' => 'error', 'message' => 'Erro ao processar requisição']);
}

// Cancelar plano
if (isset($data['action']) && $data['action'] === 'cancelar_plano') {
    $resultado = $pagamentoController->cancelarPlano($id_usuario);
    
    if ($resultado['sucesso']) {
        sendJsonResponse(['status' => 'ok', 'message' => $resultado['mensagem']]);
    } else {
        sendJsonResponse(['status' => 'error', 'message' => $resultado['mensagem']]);
    }
} else {
    sendJsonResponse(['status' => 'error', 'message' => 'Ação inválida']);
}

