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
        echo json_encode(['status' => 'error', 'message' => 'Erro fatal no servidor']);
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

// Verificar se é admin
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    sendJsonResponse(['status' => 'error', 'message' => 'Acesso negado']);
}

// Carregar autoloader e classes necessárias
require_once __DIR__ . '/../app/config/autoload.php';

// Inicializar controller
$aulaController = new AulaController();

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

// Deletar aula
if (isset($data['action']) && $data['action'] === 'delete' && isset($data['id'])) {
    try {
        $id = intval($data['id']);
        $resultado = $aulaController->deletar($id);
        
        if ($resultado['sucesso']) {
            sendJsonResponse(['status' => 'ok']);
        } else {
            sendJsonResponse(['status' => 'error', 'message' => $resultado['mensagem']]);
        }
    } catch (Exception $e) {
        sendJsonResponse(['status' => 'error', 'message' => 'Erro ao deletar aula: ' . $e->getMessage()]);
    }
}

// Atualizar ou inserir aula
$local = $data['local'] ?? '';
$modalidade = $data['modalidade'] ?? '';
$lotacao = isset($data['lotacao']) && $data['lotacao'] ? intval($data['lotacao']) : null;
$professor = isset($data['professor']) && $data['professor'] ? intval($data['professor']) : null;

if ($local && $modalidade) {
    try {
        if (isset($data['id']) && $data['id']) {
            // Atualizar
            $id = intval($data['id']);
            $resultado = $aulaController->atualizar($id, $local, $modalidade, $lotacao, $professor);
            
            if ($resultado['sucesso']) {
                sendJsonResponse(['status' => 'ok']);
            } else {
                sendJsonResponse(['status' => 'error', 'message' => $resultado['mensagem']]);
            }
        } else {
            // Inserir novo
            $resultado = $aulaController->criar($local, $modalidade, $lotacao, $professor);
            
            if ($resultado['sucesso']) {
                sendJsonResponse(['status' => 'ok']);
            } else {
                sendJsonResponse(['status' => 'error', 'message' => $resultado['mensagem']]);
            }
        }
    } catch (Exception $e) {
        sendJsonResponse(['status' => 'error', 'message' => 'Erro ao processar aula: ' . $e->getMessage()]);
    }
} else {
    sendJsonResponse(['status' => 'error', 'message' => 'Local e modalidade são obrigatórios']);
}
