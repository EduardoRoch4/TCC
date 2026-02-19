<?php
session_start();

// Resposta padrão
$response = ['status' => 'error', 'message' => 'Erro desconhecido.'];

// 1. VERIFICAR SE O USUÁRIO ESTÁ LOGADO
if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Usuário não autenticado. Faça login novamente.';
    echo json_encode($response);
    exit;
}

// 2. OBTER DADOS ENVIADOS PELO JAVASCRIPT
// O JS enviará os dados como JSON, então lemos o input
$data = json_decode(file_get_contents("php://input"), true);

$id_usuario = $_SESSION['id_usuario'];
$dia = $data['dia'] ?? null;
$mes = $data['mes'] ?? null; // 1-12
$ano = $data['ano'] ?? null;
$horario = $data['horario'] ?? null; // "08:00"
$objetivo = $data['objetivo'] ?? null; // "Perda de peso"
$modalidade = $data['modalidade'] ?? null; // "Musculação"

// 3. VALIDAR DADOS
if (empty($dia) || empty($mes) || empty($ano) || empty($horario) || empty($objetivo) || empty($modalidade)) {
    $response['message'] = '⚠️ Por favor, preencha todos os campos (incluindo modalidade).';
    echo json_encode($response);
    exit;
}

// 4. FORMATAR DATA E HORA PARA O SQL (DATETIME YYYY-MM-DD HH:MM:SS)
try {
    // Formata a data: "YYYY-MM-DD"
    $data_sql = sprintf("%04d-%02d-%02d", $ano, $mes, $dia);
    // Formata a hora: "HH:MM:SS"
    $hora_sql = $horario . ":00";
    // Combina:
    $data_hora_sql = $data_sql . " " . $hora_sql;
} catch (Exception $e) {
    $response['message'] = '❌ Erro ao formatar a data.';
    echo json_encode($response);
    exit;
}

// 5. Carregar autoloader e classes necessárias
require_once __DIR__ . '/../app/config/autoload.php';

// 6. Inicializar controller
$agendamentoController = new AgendamentoController();

// 7. Criar agendamento usando o controller
$status_confirmado = "Confirmado";
$id_aula_placeholder = 1;

try {
    $resultado = $agendamentoController->criar(
        $id_usuario,
        $data_hora_sql,
        $objetivo,
        $modalidade,
        $status_confirmado,
        $id_aula_placeholder
    );
    
    if ($resultado['sucesso']) {
        $response['status'] = 'success';
        $response['message'] = '✅ Agendamento realizado com sucesso!';
    } else {
        $response['message'] = '❌ ' . $resultado['mensagem'];
    }
} catch (Exception $e) {
    $response['message'] = '❌ Erro ao processar agendamento: ' . $e->getMessage();
}

// 6. RETORNAR A RESPOSTA PARA O JAVASCRIPT
header('Content-Type: application/json');
echo json_encode($response);
?>