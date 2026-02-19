<?php
// Small admin dashboard API: returns DB counts and recent agendamentos as JSON
header('Content-Type: application/json');

session_start();
// Apenas administradores podem consultar os dados do dashboard
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado: admin requerido.']);
    exit;
}

// Carregar autoloader e classes necessárias
require_once __DIR__ . '/../app/config/autoload.php';

$usuarioDAO = new UsuarioDAO();
$professorDAO = new ProfessorDAO();
$agendamentoDAO = new AgendamentoDAO();
$usuarioController = new UsuarioController();

$response = ['status' => 'ok', 'data' => []];

// Counts: usuarios, professor, agendamentos totais, agendamentos futuros
$response['data']['total_usuarios'] = $usuarioDAO->contarTotal();
$response['data']['total_professores'] = $professorDAO->contarTotal();
$response['data']['total_agendamentos'] = $agendamentoDAO->contarTotal();
$response['data']['agendamentos_futuros'] = $agendamentoDAO->contarFuturos();

// Recent bookings (join with user name)
$agendamentos_recentes = $agendamentoDAO->listarTodos();
$recent = [];
$count = 0;
foreach ($agendamentos_recentes as $ag) {
    if ($count >= 8) break;
    $usuario = $usuarioController->buscarPorId($ag->getIdUsuario());
    $recent[] = [
        'id_agendamento' => $ag->getIdAgendamento(),
        'data_hora' => $ag->getDataHora(),
        'objetivo' => $ag->getObjetivo(),
        'modalidade' => $ag->getModalidade(),
        'status_' => $ag->getStatus(),
        'usuario' => $usuario ? $usuario->getNome() : 'N/A'
    ];
    $count++;
}
$response['data']['recent_agendamentos'] = $recent;

echo json_encode($response, JSON_UNESCAPED_UNICODE);
