<?php
/**
 * Model Professor
 * Representa a entidade Professor no sistema
 */
class Professor {
    private $id_professor;
    private $nome_professor;
    private $especializacao;
    private $id_aula;
    
    public function __construct($id_professor = null, $nome_professor = null, $especializacao = null, $id_aula = null) {
        $this->id_professor = $id_professor;
        $this->nome_professor = $nome_professor;
        $this->especializacao = $especializacao;
        $this->id_aula = $id_aula;
    }
    
    // Getters
    public function getIdProfessor() {
        return $this->id_professor;
    }
    
    public function getNomeProfessor() {
        return $this->nome_professor;
    }
    
    public function getEspecializacao() {
        return $this->especializacao;
    }
    
    public function getIdAula() {
        return $this->id_aula;
    }
    
    // Setters
    public function setIdProfessor($id_professor) {
        $this->id_professor = $id_professor;
    }
    
    public function setNomeProfessor($nome_professor) {
        $this->nome_professor = $nome_professor;
    }
    
    public function setEspecializacao($especializacao) {
        $this->especializacao = $especializacao;
    }
    
    public function setIdAula($id_aula) {
        $this->id_aula = $id_aula;
    }
}

