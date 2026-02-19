<?php
/**
 * DAO (Data Access Object) para Aula
 * Responsável por todas as operações de banco de dados relacionadas a Aula
 */
class AulaDAO {
    private $conn;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }
    
    /**
     * Busca uma aula por ID
     */
    public function buscarPorId($id_aula) {
        $stmt = $this->conn->prepare("SELECT * FROM Aulas WHERE id_aula = ?");
        $stmt->bind_param("i", $id_aula);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->mapearParaObjeto($row);
        }
        
        return null;
    }
    
    /**
     * Lista todas as aulas
     */
    public function listarTodas() {
        $result = $this->conn->query("SELECT * FROM Aulas ORDER BY id_aula");
        $aulas = [];
        
        while ($row = $result->fetch_assoc()) {
            $aulas[] = $this->mapearParaObjeto($row);
        }
        
        return $aulas;
    }
    
    /**
     * Cria uma nova aula
     */
    public function criar(Aula $aula) {
        $local = $aula->getLocal();
        $modalidade = $aula->getModalidade();
        $lotacao = $aula->getLotacaoMaxima();
        $professor = $aula->getIdProfessor();
        
        if ($lotacao && $professor) {
            $stmt = $this->conn->prepare("INSERT INTO Aulas (local_, modalidade, lotacao_maxima, id_professor) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $local, $modalidade, $lotacao, $professor);
        } elseif ($lotacao) {
            $stmt = $this->conn->prepare("INSERT INTO Aulas (local_, modalidade, lotacao_maxima) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $local, $modalidade, $lotacao);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO Aulas (local_, modalidade) VALUES (?, ?)");
            $stmt->bind_param("ss", $local, $modalidade);
        }
        
        if ($stmt->execute()) {
            $aula->setIdAula($this->conn->insert_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Atualiza uma aula existente
     */
    public function atualizar(Aula $aula) {
        $local = $aula->getLocal();
        $modalidade = $aula->getModalidade();
        $lotacao = $aula->getLotacaoMaxima();
        $professor = $aula->getIdProfessor();
        $id_aula = $aula->getIdAula();
        
        if ($lotacao && $professor) {
            $stmt = $this->conn->prepare("UPDATE Aulas SET local_ = ?, modalidade = ?, lotacao_maxima = ?, id_professor = ? WHERE id_aula = ?");
            $stmt->bind_param("ssiii", $local, $modalidade, $lotacao, $professor, $id_aula);
        } elseif ($lotacao) {
            $stmt = $this->conn->prepare("UPDATE Aulas SET local_ = ?, modalidade = ?, lotacao_maxima = ? WHERE id_aula = ?");
            $stmt->bind_param("ssii", $local, $modalidade, $lotacao, $id_aula);
        } else {
            $stmt = $this->conn->prepare("UPDATE Aulas SET local_ = ?, modalidade = ? WHERE id_aula = ?");
            $stmt->bind_param("ssi", $local, $modalidade, $id_aula);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Deleta uma aula
     */
    public function deletar($id_aula) {
        $stmt = $this->conn->prepare("DELETE FROM Aulas WHERE id_aula = ?");
        $stmt->bind_param("i", $id_aula);
        return $stmt->execute();
    }
    
    /**
     * Conta total de aulas
     */
    public function contarTotal() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM Aulas");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return intval($row['count']);
        }
        return 0;
    }
    
    /**
     * Mapeia um array associativo para um objeto Aula
     */
    private function mapearParaObjeto($row) {
        return new Aula(
            $row['id_aula'] ?? null,
            $row['local_'] ?? null,
            $row['modalidade'] ?? null,
            $row['lotacao_maxima'] ?? null,
            $row['id_professor'] ?? null,
            $row['id_funcionario'] ?? null
        );
    }
}

