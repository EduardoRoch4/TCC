<?php
/**
 * Controller para Pagamento
 * Responsável por processar requisições relacionadas a pagamentos
 */
class PagamentoController {
    private $pagamentoDAO;
    private $usuarioDAO;
    
    public function __construct() {
        $this->pagamentoDAO = new PagamentoDAO();
        $this->usuarioDAO = new UsuarioDAO();
    }
    
    /**
     * Processa o pagamento
     */
    public function processarPagamento($id_usuario, $plano, $unidade = null) {
        // Mapear valores dos planos
        $planos = [
            'BLACK' => 149.90,
            'TECH' => 119.90,
            'FIT' => 99.90
        ];
        
        $valor = isset($planos[$plano]) ? $planos[$plano] : 0;
        
        if ($id_usuario <= 0) {
            return ['sucesso' => false, 'mensagem' => 'Usuário não autenticado. Faça login primeiro!'];
        }
        
        if ($valor <= 0) {
            return ['sucesso' => false, 'mensagem' => 'Plano inválido!'];
        }
        
        // Criar objeto Pagamento
        $pagamento = new Pagamento();
        $pagamento->setIdUsuario($id_usuario);
        $pagamento->setPlano($plano);
        $pagamento->setValor($valor);
        $pagamento->setStatus('Pago');
        
        // Salvar pagamento
        if ($this->pagamentoDAO->criar($pagamento)) {
            // Se houver unidade, atualizar o campo unidade na tabela usuarios
            if (!empty($unidade)) {
                $this->usuarioDAO->atualizarUnidade($id_usuario, $unidade);
            }
            
            return ['sucesso' => true, 'mensagem' => 'Pagamento registrado com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao registrar pagamento.'];
    }
    
    /**
     * Busca o último pagamento de um usuário
     */
    public function buscarUltimoPagamento($id_usuario) {
        return $this->pagamentoDAO->buscarUltimoPorUsuario($id_usuario);
    }
    
    /**
     * Lista todos os pagamentos de um usuário
     */
    public function listarPagamentosUsuario($id_usuario) {
        return $this->pagamentoDAO->listarPorUsuario($id_usuario);
    }
    
    /**
     * Cancela o plano do usuário (atualiza o status do último pagamento para "Cancelado")
     */
    public function cancelarPlano($id_usuario) {
        $pagamento = $this->pagamentoDAO->buscarUltimoPorUsuario($id_usuario);
        
        if (!$pagamento) {
            return ['sucesso' => false, 'mensagem' => 'Nenhum plano encontrado para cancelar.'];
        }
        
        // Verificar se já está cancelado
        $status_atual = $pagamento->getStatus();
        if ($status_atual === 'Cancelado') {
            return ['sucesso' => false, 'mensagem' => 'O plano já está cancelado.'];
        }
        
        // Verificar e adicionar 'Cancelado' ao ENUM se não existir
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $result = $conn->query("SHOW COLUMNS FROM pagamentos WHERE Field = 'status'");
        
        $enum_has_cancelado = false;
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $type = $row['Type'];
            
            // Verificar se 'Cancelado' existe no ENUM (pode estar com aspas simples ou duplas)
            if (preg_match("/['\"]Cancelado['\"]/i", $type)) {
                $enum_has_cancelado = true;
            } else {
                // Adicionar 'Cancelado' ao ENUM
                $sql = "ALTER TABLE pagamentos MODIFY COLUMN status ENUM('Pago', 'Pendente', 'Cancelado') DEFAULT 'Pendente'";
                if ($conn->query($sql)) {
                    $enum_has_cancelado = true;
                } else {
                    // Se falhar ao adicionar 'Cancelado', usar 'Pendente' como fallback
                    $pagamento->setStatus('Pendente');
                    if ($this->pagamentoDAO->atualizar($pagamento)) {
                        return ['sucesso' => true, 'mensagem' => 'Plano marcado como pendente. Entre em contato com o administrador para cancelamento completo.'];
                    }
                    return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar status do plano: ' . $conn->error];
                }
            }
        }
        
        // Se o ENUM não suporta 'Cancelado' e não conseguimos adicionar, usar 'Pendente'
        if (!$enum_has_cancelado) {
            $pagamento->setStatus('Pendente');
            if ($this->pagamentoDAO->atualizar($pagamento)) {
                return ['sucesso' => true, 'mensagem' => 'Plano marcado como pendente. Entre em contato com o administrador para cancelamento completo.'];
            }
            return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar status do plano.'];
        }
        
        // Atualizar status para 'Cancelado'
        $pagamento->setStatus('Cancelado');
        
        if ($this->pagamentoDAO->atualizar($pagamento)) {
            return ['sucesso' => true, 'mensagem' => 'Plano cancelado com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao cancelar plano.'];
    }
}

