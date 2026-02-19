<?php
/**
 * Controller para Aula
 * Responsável por processar requisições relacionadas a aulas
 */
class AulaController {
    private $aulaDAO;
    
    public function __construct() {
        $this->aulaDAO = new AulaDAO();
    }
    
    /**
     * Cria uma nova aula
     */
    public function criar($local, $modalidade, $lotacao = null, $professor = null) {
        if (empty($local) || empty($modalidade)) {
            return ['sucesso' => false, 'mensagem' => 'Local e modalidade são obrigatórios'];
        }
        
        $aula = new Aula();
        $aula->setLocal($local);
        $aula->setModalidade($modalidade);
        if ($lotacao) {
            $aula->setLotacaoMaxima($lotacao);
        }
        if ($professor) {
            $aula->setIdProfessor($professor);
        }
        
        if ($this->aulaDAO->criar($aula)) {
            return ['sucesso' => true, 'mensagem' => 'Aula cadastrada com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao cadastrar aula.'];
    }
    
    /**
     * Atualiza uma aula existente
     */
    public function atualizar($id_aula, $local, $modalidade, $lotacao = null, $professor = null) {
        if (empty($local) || empty($modalidade)) {
            return ['sucesso' => false, 'mensagem' => 'Local e modalidade são obrigatórios'];
        }
        
        $aula = $this->aulaDAO->buscarPorId($id_aula);
        if (!$aula) {
            return ['sucesso' => false, 'mensagem' => 'Aula não encontrada!'];
        }
        
        $aula->setLocal($local);
        $aula->setModalidade($modalidade);
        if ($lotacao) {
            $aula->setLotacaoMaxima($lotacao);
        }
        if ($professor) {
            $aula->setIdProfessor($professor);
        }
        
        if ($this->aulaDAO->atualizar($aula)) {
            return ['sucesso' => true, 'mensagem' => 'Aula atualizada com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar aula.'];
    }
    
    /**
     * Deleta uma aula
     */
    public function deletar($id_aula) {
        if ($this->aulaDAO->deletar($id_aula)) {
            return ['sucesso' => true, 'mensagem' => 'Aula deletada com sucesso!'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao deletar aula.'];
    }
    
    /**
     * Busca uma aula por ID
     */
    public function buscarPorId($id_aula) {
        return $this->aulaDAO->buscarPorId($id_aula);
    }
    
    /**
     * Lista todas as aulas
     * @return Aula[]
     */
    public function listarTodas() {
        return $this->aulaDAO->listarTodas();
    }
}

