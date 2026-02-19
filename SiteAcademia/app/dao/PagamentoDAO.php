<?php
/**
 * DAO (Data Access Object) para Pagamento
 * Responsável por todas as operações de banco de dados relacionadas a Pagamento
 */
class PagamentoDAO {
    private $conn;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }
    
    /**
     * Busca um pagamento por ID
     */
    public function buscarPorId($id_pagamento) {
        $stmt = $this->conn->prepare("SELECT * FROM pagamentos WHERE id_pagamento = ?");
        $stmt->bind_param("i", $id_pagamento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->mapearParaObjeto($row);
        }
        
        return null;
    }
    
    /**
     * Busca o último pagamento de um usuário
     */
    public function buscarUltimoPorUsuario($id_usuario) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM pagamentos WHERE id_usuario = ? 
             ORDER BY data_pagamento DESC LIMIT 1"
        );
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->mapearParaObjeto($row);
        }
        
        return null;
    }
    
    /**
     * Lista todos os pagamentos de um usuário
     */
    public function listarPorUsuario($id_usuario) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM pagamentos WHERE id_usuario = ? 
             ORDER BY data_pagamento DESC"
        );
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $pagamentos = [];
        while ($row = $result->fetch_assoc()) {
            $pagamentos[] = $this->mapearParaObjeto($row);
        }
        
        return $pagamentos;
    }
    
    /**
     * Cria um novo pagamento
     */
    public function criar(Pagamento $pagamento) {
        $stmt = $this->conn->prepare(
            "INSERT INTO pagamentos (id_usuario, plano, valor, status, data_pagamento) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        
        $id_usuario = $pagamento->getIdUsuario();
        $plano = $pagamento->getPlano();
        $valor = $pagamento->getValor();
        $status = $pagamento->getStatus();
        
        $stmt->bind_param("isds", $id_usuario, $plano, $valor, $status);
        
        if ($stmt->execute()) {
            $pagamento->setIdPagamento($this->conn->insert_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Atualiza um pagamento existente
     */
    public function atualizar(Pagamento $pagamento) {
        $stmt = $this->conn->prepare(
            "UPDATE pagamentos SET id_usuario = ?, plano = ?, valor = ?, 
             status = ?, data_pagamento = ? WHERE id_pagamento = ?"
        );
        
        $id_usuario = $pagamento->getIdUsuario();
        $plano = $pagamento->getPlano();
        $valor = $pagamento->getValor();
        $status = $pagamento->getStatus();
        $data_pagamento = $pagamento->getDataPagamento();
        $id_pagamento = $pagamento->getIdPagamento();
        
        // Tipos: i (id_usuario), s (plano), d (valor), s (status), s (data_pagamento), i (id_pagamento)
        $stmt->bind_param("isdssi", $id_usuario, $plano, $valor, $status, $data_pagamento, $id_pagamento);
        
        return $stmt->execute();
    }
    
    /**
     * Deleta um pagamento
     */
    public function deletar($id_pagamento) {
        $stmt = $this->conn->prepare("DELETE FROM pagamentos WHERE id_pagamento = ?");
        $stmt->bind_param("i", $id_pagamento);
        return $stmt->execute();
    }
    
    /**
     * Lista todos os pagamentos
     */
    public function listarTodos() {
        $result = $this->conn->query("SELECT * FROM pagamentos ORDER BY data_pagamento DESC");
        $pagamentos = [];
        
        while ($row = $result->fetch_assoc()) {
            $pagamentos[] = $this->mapearParaObjeto($row);
        }
        
        return $pagamentos;
    }
    
    /**
     * Mapeia um array associativo para um objeto Pagamento
     */
    private function mapearParaObjeto($row) {
        return new Pagamento(
            $row['id_pagamento'] ?? null,
            $row['id_usuario'] ?? null,
            $row['plano'] ?? null,
            $row['valor'] ?? null,
            $row['status'] ?? null,
            $row['data_pagamento'] ?? null
        );
    }
}

