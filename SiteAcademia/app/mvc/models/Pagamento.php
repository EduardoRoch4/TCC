<?php
/**
 * Model Pagamento
 * Representa a entidade Pagamento no sistema
 */
class Pagamento {
    private $id_pagamento;
    private $id_usuario;
    private $plano;
    private $valor;
    private $status;
    private $data_pagamento;
    
    public function __construct($id_pagamento = null, $id_usuario = null, $plano = null, 
                                $valor = null, $status = null, $data_pagamento = null) {
        $this->id_pagamento = $id_pagamento;
        $this->id_usuario = $id_usuario;
        $this->plano = $plano;
        $this->valor = $valor;
        $this->status = $status;
        $this->data_pagamento = $data_pagamento;
    }
    
    // Getters
    public function getIdPagamento() {
        return $this->id_pagamento;
    }
    
    public function getIdUsuario() {
        return $this->id_usuario;
    }
    
    public function getPlano() {
        return $this->plano;
    }
    
    public function getValor() {
        return $this->valor;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getDataPagamento() {
        return $this->data_pagamento;
    }
    
    // Setters
    public function setIdPagamento($id_pagamento) {
        $this->id_pagamento = $id_pagamento;
    }
    
    public function setIdUsuario($id_usuario) {
        $this->id_usuario = $id_usuario;
    }
    
    public function setPlano($plano) {
        $this->plano = $plano;
    }
    
    public function setValor($valor) {
        $this->valor = $valor;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function setDataPagamento($data_pagamento) {
        $this->data_pagamento = $data_pagamento;
    }
}

