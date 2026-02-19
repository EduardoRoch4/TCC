<?php
/**
 * Controller para Professor
 * Responsável por processar requisições relacionadas a professores
 */
class ProfessorController {
    private $professorDAO;
    
    public function __construct() {
        $this->professorDAO = new ProfessorDAO();
    }
    
    /**
     * Cria um novo professor e cria um usuário com perfil admin para ele
     */
    public function criar($nome, $especialidade, $email = null) {
        if (empty($nome)) {
            return ['sucesso' => false, 'mensagem' => 'Nome é obrigatório'];
        }

        $professor = new Professor();
        $professor->setNomeProfessor($nome);
        $professor->setEspecializacao($especialidade);

        if ($this->professorDAO->criar($professor)) {
            // Tentar criar um usuário admin correspondente (se email fornecido)
            try {
                if (!empty($email)) {
                    $usuarioDAO = new UsuarioDAO();

                    // Verificar existência por nome ou email
                    $existNome = $usuarioDAO->buscarPorNome($nome);
                    $existEmail = $usuarioDAO->buscarPorEmail($email);

                    if ($existNome || $existEmail) {
                        return ['sucesso' => true, 'mensagem' => 'Professor cadastrado. Não foi criado usuário admin: nome ou email já existe.'];
                    }

                    $usuario = new Usuario();
                    $usuario->setNome($nome);
                    $usuario->setEmail($email);
                    $usuario->setPerfil('admin');
                    // Deixar senha nula/sem valor para exigir redefinição posteriormente
                    $usuario->setSenha(null);

                    $usuarioDAO->criar($usuario);
                }
            } catch (Exception $e) {
                // Se falhar ao criar usuário, retornar sucesso parcial
                return ['sucesso' => true, 'mensagem' => 'Professor cadastrado, porém falha ao criar usuário admin: ' . $e->getMessage()];
            }

            return ['sucesso' => true, 'mensagem' => 'Professor cadastrado com sucesso!'];
        }

        return ['sucesso' => false, 'mensagem' => 'Erro ao cadastrar professor.'];
    }
    
    /**
     * Atualiza um professor existente
     */
    public function atualizar($id_professor, $nome, $especialidade) {
        if (empty($nome)) {
            return ['sucesso' => false, 'mensagem' => 'Nome é obrigatório'];
        }
        
        $professor = $this->professorDAO->buscarPorId($id_professor);
        if (!$professor) {
            return ['sucesso' => false, 'mensagem' => 'Professor não encontrado!'];
        }
        
        $professor->setNomeProfessor($nome);
        $professor->setEspecializacao($especialidade);
        
        if ($this->professorDAO->atualizar($professor)) {
            return ['sucesso' => true, 'mensagem' => 'Professor atualizado com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar professor.'];
    }
    
    /**
     * Deleta um professor
     */
    public function deletar($id_professor) {
        if ($this->professorDAO->deletarComCascata($id_professor)) {
            return ['sucesso' => true, 'mensagem' => 'Professor deletado com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao deletar professor.'];
    }
    
    /**
     * Busca um professor por ID
     */
    public function buscarPorId($id_professor) {
        return $this->professorDAO->buscarPorId($id_professor);
    }
    
    /**
     * Lista todos os professores
     * @return Professor[]
     */
    public function listarTodos() {
        return $this->professorDAO->listarTodos();
    }
}

