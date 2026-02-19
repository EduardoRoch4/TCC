<?php
/**
 * Controller para Agendamento
 * Responsável por processar requisições relacionadas a agendamentos
 */
class AgendamentoController {
    private $agendamentoDAO;
    
    public function __construct() {
        $this->agendamentoDAO = new AgendamentoDAO();
    }
    
    /**
     * Cria um novo agendamento
     */
    public function criar($id_usuario, $data_hora, $objetivo, $modalidade, $status = 'Confirmado', $id_aula = 1) {
        // Validar objetivo (deve ser um dos valores do ENUM)
        $objetivos_validos = ['Perda de peso', 'Ganho de Massa', 'Hipertrofia', 'Saúde'];
        if (!in_array($objetivo, $objetivos_validos)) {
            return ['sucesso' => false, 'mensagem' => 'Objetivo inválido!'];
        }
        
        // Criar objeto Agendamento
        $agendamento = new Agendamento();
        $agendamento->setIdUsuario($id_usuario);
        $agendamento->setDataHora($data_hora);
        $agendamento->setObjetivo($objetivo);
        $agendamento->setModalidade($modalidade);
        $agendamento->setStatus($status);
        $agendamento->setIdAula($id_aula);
        
        // Salvar agendamento
        if ($this->agendamentoDAO->criar($agendamento)) {
            return ['sucesso' => true, 'mensagem' => 'Agendamento criado com sucesso!', 'agendamento' => $agendamento];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao criar agendamento.'];
    }
    
    /**
     * Atualiza um agendamento existente
     */
    public function atualizar($id_agendamento, $id_usuario, $data_hora, $objetivo, $modalidade, $status, $id_aula = 1) {
        // Validar objetivo
        $objetivos_validos = ['Perda de peso', 'Ganho de Massa', 'Hipertrofia', 'Saúde'];
        if (!in_array($objetivo, $objetivos_validos)) {
            return ['sucesso' => false, 'mensagem' => 'Objetivo inválido!'];
        }
        
        // Buscar agendamento existente
        $agendamento = $this->agendamentoDAO->buscarPorId($id_agendamento);
        if (!$agendamento) {
            return ['sucesso' => false, 'mensagem' => 'Agendamento não encontrado!'];
        }
        
        // Atualizar dados
        $agendamento->setIdUsuario($id_usuario);
        $agendamento->setDataHora($data_hora);
        $agendamento->setObjetivo($objetivo);
        $agendamento->setModalidade($modalidade);
        $agendamento->setStatus($status);
        $agendamento->setIdAula($id_aula);
        
        // Salvar alterações
        if ($this->agendamentoDAO->atualizar($agendamento)) {
            return ['sucesso' => true, 'mensagem' => 'Agendamento atualizado com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar agendamento.'];
    }
    
    /**
     * Deleta um agendamento
     */
    public function deletar($id_agendamento) {
        if ($this->agendamentoDAO->deletar($id_agendamento)) {
            return ['sucesso' => true, 'mensagem' => 'Agendamento deletado com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao deletar agendamento.'];
    }
    
    /**
     * Busca um agendamento por ID
     */
    public function buscarPorId($id_agendamento) {
        return $this->agendamentoDAO->buscarPorId($id_agendamento);
    }
    
    /**
     * Lista todos os agendamentos de um usuário
     */
    public function listarPorUsuario($id_usuario) {
        return $this->agendamentoDAO->listarPorUsuario($id_usuario);
    }
    
    /**
     * Lista todos os agendamentos
     */
    public function listarTodos() {
        return $this->agendamentoDAO->listarTodos();
    }
}

