<?php
/**
 * DAO (Data Access Object) para Agendamento
 * Responsável por todas as operações de banco de dados relacionadas a Agendamento
 */
class AgendamentoDAO {
    private $conn;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }
    
    /**
     * Busca um agendamento por ID
     */
    public function buscarPorId($id_agendamento) {
        $stmt = $this->conn->prepare("SELECT * FROM agendamentos WHERE id_agendamento = ?");
        $stmt->bind_param("i", $id_agendamento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->mapearParaObjeto($row);
        }
        
        return null;
    }
    
    /**
     * Lista todos os agendamentos de um usuário
     */
    public function listarPorUsuario($id_usuario) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM agendamentos WHERE id_usuario = ? 
             ORDER BY data_hora DESC LIMIT 50"
        );
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $agendamentos = [];
        while ($row = $result->fetch_assoc()) {
            $agendamentos[] = $this->mapearParaObjeto($row);
        }
        
        return $agendamentos;
    }
    
    /**
     * Lista todos os agendamentos
     */
    public function listarTodos() {
        $result = $this->conn->query(
            "SELECT a.*, u.nome as nome_usuario 
             FROM agendamentos a 
             LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario 
             ORDER BY a.data_hora DESC"
        );
        
        $agendamentos = [];
        while ($row = $result->fetch_assoc()) {
            $agendamentos[] = $this->mapearParaObjeto($row);
        }
        
        return $agendamentos;
    }
    
    /**
     * Conta total de agendamentos
     */
    public function contarTotal() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM agendamentos");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return intval($row['count']);
        }
        return 0;
    }
    
    /**
     * Cria um novo agendamento
     */
    public function criar(Agendamento $agendamento) {
        $stmt = $this->conn->prepare(
            "INSERT INTO agendamentos (id_usuario, data_hora, objetivo, modalidade, status_, id_aula) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $id_usuario = $agendamento->getIdUsuario();
        $data_hora = $agendamento->getDataHora();
        $objetivo = $agendamento->getObjetivo();
        $modalidade = $agendamento->getModalidade();
        $status_ = $agendamento->getStatus();
        $id_aula = $agendamento->getIdAula() ?? 1; // Default para 1 se não especificado
        
        $stmt->bind_param("issssi", $id_usuario, $data_hora, $objetivo, $modalidade, $status_, $id_aula);
        
        if ($stmt->execute()) {
            $agendamento->setIdAgendamento($this->conn->insert_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Atualiza um agendamento existente
     */
    public function atualizar(Agendamento $agendamento) {
        $stmt = $this->conn->prepare(
            "UPDATE agendamentos SET id_usuario = ?, data_hora = ?, objetivo = ?, 
             modalidade = ?, status_ = ?, id_aula = ? WHERE id_agendamento = ?"
        );
        
        $id_usuario = $agendamento->getIdUsuario();
        $data_hora = $agendamento->getDataHora();
        $objetivo = $agendamento->getObjetivo();
        $modalidade = $agendamento->getModalidade();
        $status_ = $agendamento->getStatus();
        $id_aula = $agendamento->getIdAula() ?? 1;
        $id_agendamento = $agendamento->getIdAgendamento();
        
        $stmt->bind_param("issssii", $id_usuario, $data_hora, $objetivo, $modalidade, $status_, $id_aula, $id_agendamento);
        
        return $stmt->execute();
    }
    
    /**
     * Deleta um agendamento
     */
    public function deletar($id_agendamento) {
        $stmt = $this->conn->prepare("DELETE FROM agendamentos WHERE id_agendamento = ?");
        $stmt->bind_param("i", $id_agendamento);
        return $stmt->execute();
    }
    
    /**
     * Conta agendamentos por status
     */
    public function contarPorStatus($status) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM agendamentos WHERE status_ = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return intval($row['count']);
        }
        return 0;
    }
    
    /**
     * Conta agendamentos futuros
     */
    public function contarFuturos() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM agendamentos WHERE data_hora >= NOW()");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return intval($row['count']);
        }
        return 0;
    }
    
    /**
     * Busca agendamentos por modalidade
     */
    public function listarPorModalidade() {
        $result = $this->conn->query(
            "SELECT modalidade, COUNT(*) as count 
             FROM agendamentos 
             WHERE modalidade IS NOT NULL 
             GROUP BY modalidade 
             ORDER BY count DESC"
        );
        $modalidades = [];
        while ($row = $result->fetch_assoc()) {
            $modalidades[] = $row;
        }
        return $modalidades;
    }
    
    /**
     * Busca top horários mais agendados
     */
    public function listarTopHorarios($limit = 5) {
        $stmt = $this->conn->prepare(
            "SELECT HOUR(data_hora) as hora, COUNT(*) as count 
             FROM agendamentos 
             GROUP BY HOUR(data_hora) 
             ORDER BY count DESC 
             LIMIT ?"
        );
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $horarios = [];
        while ($row = $result->fetch_assoc()) {
            $hora = str_pad($row['hora'], 2, '0', STR_PAD_LEFT);
            $horarios[] = ['hora' => $hora . ':00', 'count' => intval($row['count'])];
        }
        return $horarios;
    }
    
    /**
     * Mapeia um array associativo para um objeto Agendamento
     */
    private function mapearParaObjeto($row) {
        return new Agendamento(
            $row['id_agendamento'] ?? null,
            $row['id_usuario'] ?? null,
            $row['data_hora'] ?? null,
            $row['objetivo'] ?? null,
            $row['modalidade'] ?? null,
            $row['status_'] ?? null,
            $row['id_aula'] ?? null
        );
    }
}

