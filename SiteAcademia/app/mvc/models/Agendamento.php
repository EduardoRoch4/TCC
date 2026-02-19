<?php
/**
 * Model Agendamento
 * Representa a entidade Agendamento no sistema
 */
class Agendamento {
    private $id_agendamento;
    private $id_usuario;
    private $data_hora;
    private $objetivo;
    private $modalidade;
    private $status_;
    private $id_aula;
    
    public function __construct($id_agendamento = null, $id_usuario = null, $data_hora = null, 
                                $objetivo = null, $modalidade = null, $status_ = null, $id_aula = null) {
        $this->id_agendamento = $id_agendamento;
        $this->id_usuario = $id_usuario;
        $this->data_hora = $data_hora;
        $this->objetivo = $objetivo;
        $this->modalidade = $modalidade;
        $this->status_ = $status_;
        $this->id_aula = $id_aula;
    }
    
    // Getters
    public function getIdAgendamento() {
        return $this->id_agendamento;
    }
    
    public function getIdUsuario() {
        return $this->id_usuario;
    }
    
    public function getDataHora() {
        return $this->data_hora;
    }
    
    public function getObjetivo() {
        return $this->objetivo;
    }
    
    public function getModalidade() {
        return $this->modalidade;
    }
    
    public function getStatus() {
        return $this->status_;
    }
    
    public function getIdAula() {
        return $this->id_aula;
    }
    
    // Setters
    public function setIdAgendamento($id_agendamento) {
        $this->id_agendamento = $id_agendamento;
    }
    
    public function setIdUsuario($id_usuario) {
        $this->id_usuario = $id_usuario;
    }
    
    public function setDataHora($data_hora) {
        $this->data_hora = $data_hora;
    }
    
    public function setObjetivo($objetivo) {
        $this->objetivo = $objetivo;
    }
    
    public function setModalidade($modalidade) {
        $this->modalidade = $modalidade;
    }
    
    public function setStatus($status_) {
        $this->status_ = $status_;
    }
    
    public function setIdAula($id_aula) {
        $this->id_aula = $id_aula;
    }
}

