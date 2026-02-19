<?php
/**
 * DAO (Data Access Object) para Usuario
 * Responsável por todas as operações de banco de dados relacionadas a Usuario
 */
class UsuarioDAO {
    private $conn;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }
    
    /**
     * Busca um usuário por ID
     */
    public function buscarPorId($id_usuario) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
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
     * Busca um usuário por nome (usado no login)
     */
    public function buscarPorNome($nome) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE nome = ?");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->mapearParaObjeto($row);
        }
        
        return null;
    }
    
    /**
     * Busca um usuário por email
     */
    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->mapearParaObjeto($row);
        }
        
        return null;
    }
    
    /**
     * Cria um novo usuário
     */
    public function criar(Usuario $usuario) {
        // Verificar se a coluna unidade existe
        $tem_unidade = $this->colunaUnidadeExiste();
        
        if ($tem_unidade) {
            $stmt = $this->conn->prepare(
                "INSERT INTO usuarios (nome, email, senha, perfil, foto, id_perfil, unidade) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            $nome = $usuario->getNome();
            $email = $usuario->getEmail();
            $senha = $usuario->getSenha();
            $perfil = $usuario->getPerfil();
            $foto = $usuario->getFoto();
            $id_perfil = $usuario->getIdPerfil();
            $unidade = $usuario->getUnidade();
            
            $stmt->bind_param("sssssis", $nome, $email, $senha, $perfil, $foto, $id_perfil, $unidade);
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO usuarios (nome, email, senha, perfil, foto, id_perfil) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $nome = $usuario->getNome();
            $email = $usuario->getEmail();
            $senha = $usuario->getSenha();
            $perfil = $usuario->getPerfil();
            $foto = $usuario->getFoto();
            $id_perfil = $usuario->getIdPerfil();
            
            $stmt->bind_param("sssssi", $nome, $email, $senha, $perfil, $foto, $id_perfil);
        }
        
        if ($stmt->execute()) {
            $usuario->setIdUsuario($this->conn->insert_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Atualiza um usuário existente
     */
    public function atualizar(Usuario $usuario) {
        // Verificar se a coluna unidade existe
        $tem_unidade = $this->colunaUnidadeExiste();
        
        if ($tem_unidade) {
            $stmt = $this->conn->prepare(
                "UPDATE usuarios SET nome = ?, email = ?, senha = ?, perfil = ?, 
                 foto = ?, id_perfil = ?, unidade = ? WHERE id_usuario = ?"
            );
            
            $nome = $usuario->getNome();
            $email = $usuario->getEmail();
            $senha = $usuario->getSenha();
            $perfil = $usuario->getPerfil();
            $foto = $usuario->getFoto();
            $id_perfil = $usuario->getIdPerfil();
            $unidade = $usuario->getUnidade();
            $id_usuario = $usuario->getIdUsuario();
            
            $stmt->bind_param("sssssisi", $nome, $email, $senha, $perfil, $foto, $id_perfil, $unidade, $id_usuario);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE usuarios SET nome = ?, email = ?, senha = ?, perfil = ?, 
                 foto = ?, id_perfil = ? WHERE id_usuario = ?"
            );
            
            $nome = $usuario->getNome();
            $email = $usuario->getEmail();
            $senha = $usuario->getSenha();
            $perfil = $usuario->getPerfil();
            $foto = $usuario->getFoto();
            $id_perfil = $usuario->getIdPerfil();
            $id_usuario = $usuario->getIdUsuario();
            
            $stmt->bind_param("sssssii", $nome, $email, $senha, $perfil, $foto, $id_perfil, $id_usuario);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Atualiza apenas a unidade do usuário
     */
    public function atualizarUnidade($id_usuario, $unidade) {
        // Verificar se o campo unidade existe
        $check_column = $this->conn->query("SHOW COLUMNS FROM usuarios LIKE 'unidade'");
        if ($check_column && $check_column->num_rows > 0) {
            $stmt = $this->conn->prepare("UPDATE usuarios SET unidade = ? WHERE id_usuario = ?");
            $stmt->bind_param("si", $unidade, $id_usuario);
            return $stmt->execute();
        }
        return false;
    }
    
    /**
     * Atualiza apenas a senha do usuário
     */
    public function atualizarSenha($id_usuario, $senha_hash) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET senha = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $senha_hash, $id_usuario);
        return $stmt->execute();
    }
    
    /**
     * Deleta um usuário
     */
    public function deletar($id_usuario) {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        return $stmt->execute();
    }
    
    /**
     * Lista todos os usuários
     */
    public function listarTodos() {
        $result = $this->conn->query("SELECT * FROM usuarios ORDER BY nome");
        $usuarios = [];
        
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $this->mapearParaObjeto($row);
        }
        
        return $usuarios;
    }
    
    /**
     * Conta total de usuários
     */
    public function contarTotal() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM usuarios");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return intval($row['count']);
        }
        return 0;
    }
    
    /**
     * Verifica se a coluna unidade existe na tabela usuarios
     * @return bool
     */
    private function colunaUnidadeExiste() {
        $check_column = $this->conn->query("SHOW COLUMNS FROM usuarios LIKE 'unidade'");
        return $check_column && $check_column->num_rows > 0;
    }
    
    /**
     * Mapeia um array associativo para um objeto Usuario
     */
    private function mapearParaObjeto($row) {
        return new Usuario(
            $row['id_usuario'] ?? null,
            $row['nome'] ?? null,
            $row['email'] ?? null,
            $row['senha'] ?? null,
            $row['perfil'] ?? null,
            $row['foto'] ?? null,
            $row['id_perfil'] ?? null,
            $row['unidade'] ?? null
        );
    }
}

