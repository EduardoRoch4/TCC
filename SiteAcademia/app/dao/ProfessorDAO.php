<?php
/**
 * DAO (Data Access Object) para Professor
 * Responsável por todas as operações de banco de dados relacionadas a Professor
 */
class ProfessorDAO {
    private $conn;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }
    
    /**
     * Busca um professor por ID
     */
    public function buscarPorId($id_professor) {
        $stmt = $this->conn->prepare("SELECT * FROM professor WHERE id_professor = ?");
        $stmt->bind_param("i", $id_professor);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->mapearParaObjeto($row);
        }
        
        return null;
    }
    
    /**
     * Lista todos os professores
     */
    public function listarTodos() {
        $result = $this->conn->query("SELECT * FROM professor ORDER BY nome_professor");
        $professores = [];
        
        while ($row = $result->fetch_assoc()) {
            $professores[] = $this->mapearParaObjeto($row);
        }
        
        return $professores;
    }
    
    /**
     * Cria um novo professor
     */
    public function criar(Professor $professor) {
        // Verificar se a tabela tem campo email
        $check_email = $this->conn->query("SHOW COLUMNS FROM professor LIKE 'email'");
        $has_email = $check_email && $check_email->num_rows > 0;
        
        if ($has_email) {
            // Verificar se o campo permite NULL
            $email_info = $this->conn->query("SHOW COLUMNS FROM professor WHERE Field = 'email'");
            $email_row = $email_info->fetch_assoc();
            $email_allows_null = ($email_row['Null'] === 'YES');
            
            $email = $professor->getNomeProfessor() . '@techfit.com';
            
            if ($email_allows_null) {
                $stmt = $this->conn->prepare("INSERT INTO professor (nome_professor, especializacao, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $professor->getNomeProfessor(), $professor->getEspecializacao(), $email);
            } else {
                $stmt = $this->conn->prepare("INSERT INTO professor (nome_professor, especializacao, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $professor->getNomeProfessor(), $professor->getEspecializacao(), $email);
            }
        } else {
            $stmt = $this->conn->prepare("INSERT INTO professor (nome_professor, especializacao) VALUES (?, ?)");
            $stmt->bind_param("ss", $professor->getNomeProfessor(), $professor->getEspecializacao());
        }
        
        if ($stmt->execute()) {
            $professor->setIdProfessor($this->conn->insert_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Atualiza um professor existente
     */
    public function atualizar(Professor $professor) {
        // Verificar se a tabela tem campo email
        $check_email = $this->conn->query("SHOW COLUMNS FROM professor LIKE 'email'");
        $has_email = $check_email && $check_email->num_rows > 0;
        
        if ($has_email) {
            // Buscar email atual ou gerar um novo
            $email_info = $this->conn->query("SHOW COLUMNS FROM professor WHERE Field = 'email'");
            $email_row = $email_info->fetch_assoc();
            $email_allows_null = ($email_row['Null'] === 'YES');
            
            $email = $professor->getNomeProfessor() . '@techfit.com';
            
            $stmt = $this->conn->prepare("UPDATE professor SET nome_professor = ?, especializacao = ?, email = ? WHERE id_professor = ?");
            $stmt->bind_param("sssi", $professor->getNomeProfessor(), $professor->getEspecializacao(), $email, $professor->getIdProfessor());
        } else {
            $stmt = $this->conn->prepare("UPDATE professor SET nome_professor = ?, especializacao = ? WHERE id_professor = ?");
            $stmt->bind_param("ssi", $professor->getNomeProfessor(), $professor->getEspecializacao(), $professor->getIdProfessor());
        }
        
        return $stmt->execute();
    }
    
    /**
     * Deleta um professor e suas aulas associadas
     */
    public function deletarComCascata($id_professor) {
        try {
            // Desabilitar verificação de foreign keys temporariamente
            $this->conn->query("SET FOREIGN_KEY_CHECKS = 0");
            
            // Deletar aulas associadas
            $stmt_del_aulas = $this->conn->prepare("DELETE FROM Aulas WHERE id_professor = ?");
            if ($stmt_del_aulas) {
                $stmt_del_aulas->bind_param("i", $id_professor);
                $stmt_del_aulas->execute();
                $stmt_del_aulas->close();
            }
            
            // Deletar o professor
            $stmt = $this->conn->prepare("DELETE FROM professor WHERE id_professor = ?");
            $stmt->bind_param("i", $id_professor);
            
            $resultado = $stmt->execute();
            
            // Reabilitar verificação de foreign keys
            $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");
            
            $stmt->close();
            
            return $resultado;
        } catch (Exception $e) {
            // Reabilitar verificação de foreign keys em caso de exceção
            try {
                $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");
            } catch (Exception $e2) {}
            return false;
        }
    }
    
    /**
     * Conta total de professores
     */
    public function contarTotal() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM professor");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return intval($row['count']);
        }
        return 0;
    }
    
    /**
     * Mapeia um array associativo para um objeto Professor
     */
    private function mapearParaObjeto($row) {
        return new Professor(
            $row['id_professor'] ?? null,
            $row['nome_professor'] ?? null,
            $row['especializacao'] ?? null,
            $row['id_aula'] ?? null
        );
    }
}

