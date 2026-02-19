<?php
/**
 * Controller para Usuario
 * Responsável por processar requisições relacionadas a usuários
 */
class UsuarioController {
    private $usuarioDAO;
    
    public function __construct() {
        $this->usuarioDAO = new UsuarioDAO();
    }
    
    /**
     * Realiza o login do usuário
     */
    public function login($nome, $senha) {
        $usuario = $this->usuarioDAO->buscarPorNome($nome);
        
        if (!$usuario) {
            return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado!'];
        }
        
        // Verificar senha
        $senha_hash = $usuario->getSenha();
        
        // Se a senha não está configurada (null ou vazia)
        if (empty($senha_hash)) {
            return ['sucesso' => false, 'mensagem' => 'Senha não configurada para este usuário. Entre em contato com o administrador.', 'redefinir_senha' => true, 'usuario' => $usuario];
        }
        
        // Se a senha está em texto plano (não hash), fazer hash e atualizar
        if (strlen($senha_hash) < 60) { // Hash bcrypt tem 60 caracteres
            if ($senha_hash === $senha) {
                // Senha correta, fazer hash e atualizar
                $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
                $this->usuarioDAO->atualizarSenha($usuario->getIdUsuario(), $novo_hash);
                return ['sucesso' => true, 'usuario' => $usuario];
            } else {
                return ['sucesso' => false, 'mensagem' => 'Senha incorreta!'];
            }
        }
        
        // Verificar senha com password_verify
        if (password_verify($senha, $senha_hash)) {
            return ['sucesso' => true, 'usuario' => $usuario];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Senha incorreta!'];
    }
    
    /**
     * Cadastra um novo usuário
     */
    public function cadastrar($nome, $email, $senha, $perfil = 'aluno') {
        // Verificar se o usuário já existe
        $usuario_existente = $this->usuarioDAO->buscarPorNome($nome);
        if ($usuario_existente) {
            return ['sucesso' => false, 'mensagem' => 'Usuário já existe!'];
        }
        
        // Verificar se o email já existe
        $email_existente = $this->usuarioDAO->buscarPorEmail($email);
        if ($email_existente) {
            return ['sucesso' => false, 'mensagem' => 'Email já cadastrado!'];
        }
        
        // Criar hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Criar objeto Usuario
        $usuario = new Usuario();
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setSenha($senha_hash);
        $usuario->setPerfil($perfil);
        
        // Salvar usuário
        if ($this->usuarioDAO->criar($usuario)) {
            return ['sucesso' => true, 'mensagem' => 'Usuário cadastrado com sucesso!', 'usuario' => $usuario];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao cadastrar usuário.'];
    }
    
    /**
     * Redefine a senha do usuário
     */
    public function redefinirSenha($id_usuario, $nova_senha) {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        if ($this->usuarioDAO->atualizarSenha($id_usuario, $senha_hash)) {
            return ['sucesso' => true, 'mensagem' => 'Senha redefinida com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao redefinir senha.'];
    }
    
    /**
     * Busca um usuário por ID
     */
    public function buscarPorId($id_usuario) {
        return $this->usuarioDAO->buscarPorId($id_usuario);
    }
    
    /**
     * Atualiza a unidade do usuário
     */
    public function atualizarUnidade($id_usuario, $unidade) {
        if ($this->usuarioDAO->atualizarUnidade($id_usuario, $unidade)) {
            return ['sucesso' => true, 'mensagem' => 'Unidade atualizada com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar unidade.'];
    }
    
    /**
     * Busca um usuário por nome
     */
    public function buscarPorNome($nome) {
        return $this->usuarioDAO->buscarPorNome($nome);
    }
    
    /**
     * Deleta um usuário e todos os registros relacionados (cascata)
     */
    public function deletarComCascata($id_usuario) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Desabilitar verificação de foreign keys temporariamente
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");
            
            // Deletar de mensagens (remetente e destinatário)
            $stmt_msg = $conn->prepare("DELETE FROM mensagens WHERE id_usuario_remetente = ? OR id_usuario_destinatario = ?");
            $stmt_msg->bind_param("ii", $id_usuario, $id_usuario);
            $stmt_msg->execute();
            $stmt_msg->close();
            
            // Deletar de outras tabelas
            $stmt_ag = $conn->prepare("DELETE FROM agendamentos WHERE id_usuario = ?");
            $stmt_ag->bind_param("i", $id_usuario);
            $stmt_ag->execute();
            $stmt_ag->close();
            
            $stmt_av = $conn->prepare("DELETE FROM avaliacoes_fisicas WHERE id_usuario = ?");
            $stmt_av->bind_param("i", $id_usuario);
            $stmt_av->execute();
            $stmt_av->close();
            
            $stmt_ac = $conn->prepare("DELETE FROM acessos WHERE id_usuario = ?");
            $stmt_ac->bind_param("i", $id_usuario);
            $stmt_ac->execute();
            $stmt_ac->close();
            
            $stmt_pag = $conn->prepare("DELETE FROM pagamentos WHERE id_usuario = ?");
            $stmt_pag->bind_param("i", $id_usuario);
            $stmt_pag->execute();
            $stmt_pag->close();
            
            // Depois, deletar o usuário
            if ($this->usuarioDAO->deletar($id_usuario)) {
                // Reabilitar verificação de foreign keys
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                return ['sucesso' => true, 'mensagem' => 'Usuário deletado com sucesso!'];
            } else {
                // Reabilitar verificação de foreign keys mesmo em caso de erro
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                return ['sucesso' => false, 'mensagem' => 'Erro ao deletar usuário.'];
            }
        } catch (Exception $e) {
            // Reabilitar verificação de foreign keys em caso de exceção
            try {
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            } catch (Exception $e2) {}
            return ['sucesso' => false, 'mensagem' => 'Erro ao deletar usuário: ' . $e->getMessage()];
        }
    }
    
    /**
     * Atualiza um usuário existente
     */
    public function atualizar($id_usuario, $nome, $email) {
        $usuario = $this->usuarioDAO->buscarPorId($id_usuario);
        
        if (!$usuario) {
            return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado!'];
        }
        
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        
        if ($this->usuarioDAO->atualizar($usuario)) {
            return ['sucesso' => true, 'mensagem' => 'Usuário atualizado com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar usuário.'];
    }
    
    /**
     * Atualiza o perfil do usuário (nome, email e senha)
     */
    public function atualizarPerfil($id_usuario, $nome, $email, $senha_atual = null, $nova_senha = null) {
        $usuario = $this->usuarioDAO->buscarPorId($id_usuario);
        
        if (!$usuario) {
            return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado!'];
        }
        
        // Se uma nova senha foi fornecida, verificar a senha atual
        if (!empty($nova_senha)) {
            if (empty($senha_atual)) {
                return ['sucesso' => false, 'mensagem' => 'É necessário informar a senha atual para alterar a senha.'];
            }
            
            // Verificar senha atual
            $senha_hash_atual = $usuario->getSenha();
            if (empty($senha_hash_atual)) {
                return ['sucesso' => false, 'mensagem' => 'Senha não configurada. Entre em contato com o administrador.'];
            }
            
            // Verificar se a senha atual está correta
            if (!password_verify($senha_atual, $senha_hash_atual)) {
                return ['sucesso' => false, 'mensagem' => 'Senha atual incorreta!'];
            }
            
            // Atualizar senha
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $usuario->setSenha($nova_senha_hash);
        }
        
        // Atualizar nome e email
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        
        if ($this->usuarioDAO->atualizar($usuario)) {
            return ['sucesso' => true, 'mensagem' => 'Perfil atualizado com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar perfil.'];
    }
    
    /**
     * Cria um novo usuário (aluno)
     */
    public function criarAluno($nome, $email) {
        // Verificar se o usuário já existe
        $usuario_existente = $this->usuarioDAO->buscarPorNome($nome);
        if ($usuario_existente) {
            return ['sucesso' => false, 'mensagem' => 'Usuário já existe!'];
        }
        
        // Verificar se o email já existe
        $email_existente = $this->usuarioDAO->buscarPorEmail($email);
        if ($email_existente) {
            return ['sucesso' => false, 'mensagem' => 'Email já cadastrado!'];
        }
        
        // Criar objeto Usuario
        $usuario = new Usuario();
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setPerfil('aluno');
        
        // Salvar usuário
        if ($this->usuarioDAO->criar($usuario)) {
            return ['sucesso' => true, 'mensagem' => 'Aluno cadastrado com sucesso!', 'usuario' => $usuario];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao cadastrar aluno.'];
    }
    
    /**
     * Lista todos os usuários
     * @return Usuario[]
     */
    public function listarTodos() {
        return $this->usuarioDAO->listarTodos();
    }
}

