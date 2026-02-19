<?php
/**
 * Model Aula
 * Representa a entidade Aula no sistema
 */
class Aula {
    private $id_aula;
    private $local_;
    private $modalidade;
    private $lotacao_maxima;
    private $id_professor;
    private $id_funcionario;
    
    public function __construct($id_aula = null, $local_ = null, $modalidade = null, 
                                $lotacao_maxima = null, $id_professor = null, $id_funcionario = null) {
        $this->id_aula = $id_aula;
        $this->local_ = $local_;
        $this->modalidade = $modalidade;
        $this->lotacao_maxima = $lotacao_maxima;
        $this->id_professor = $id_professor;
        $this->id_funcionario = $id_funcionario;
    }
    
    // Getters
    public function getIdAula() {
        return $this->id_aula;
    }
    
    public function getLocal() {
        return $this->local_;
    }
    
    public function getModalidade() {
        return $this->modalidade;
    }
    
    public function getLotacaoMaxima() {
        return $this->lotacao_maxima;
    }
    
    public function getIdProfessor() {
        return $this->id_professor;
    }
    
    public function getIdFuncionario() {
        return $this->id_funcionario;
    }
    
    // Setters
    public function setIdAula($id_aula) {
        $this->id_aula = $id_aula;
    }
    
    public function setLocal($local_) {
        $this->local_ = $local_;
    }
    
    public function setModalidade($modalidade) {
        $this->modalidade = $modalidade;
    }
    
    public function setLotacaoMaxima($lotacao_maxima) {
        $this->lotacao_maxima = $lotacao_maxima;
    }
    
    public function setIdProfessor($id_professor) {
        $this->id_professor = $id_professor;
    }
    
    public function setIdFuncionario($id_funcionario) {
        $this->id_funcionario = $id_funcionario;
    }
}

