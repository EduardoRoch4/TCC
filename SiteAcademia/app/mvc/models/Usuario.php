<?php
/**
 * Model Usuario
 * Representa a entidade Usuario no sistema
 */
class Usuario {
    private $id_usuario;
    private $nome;
    private $email;
    private $senha;
    private $perfil;
    private $foto;
    private $id_perfil;
    private $unidade;
    
    public function __construct($id_usuario = null, $nome = null, $email = null, $senha = null, 
                                $perfil = null, $foto = null, $id_perfil = null, $unidade = null) {
        $this->id_usuario = $id_usuario;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->perfil = $perfil;
        $this->foto = $foto;
        $this->id_perfil = $id_perfil;
        $this->unidade = $unidade;
    }
    
    // Getters
    public function getIdUsuario() {
        return $this->id_usuario;
    }
    
    public function getNome() {
        return $this->nome;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function getSenha() {
        return $this->senha;
    }
    
    public function getPerfil() {
        return $this->perfil;
    }
    
    public function getFoto() {
        return $this->foto;
    }
    
    public function getIdPerfil() {
        return $this->id_perfil;
    }
    
    public function getUnidade() {
        return $this->unidade;
    }
    
    // Setters
    public function setIdUsuario($id_usuario) {
        $this->id_usuario = $id_usuario;
    }
    
    public function setNome($nome) {
        $this->nome = $nome;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    
    public function setSenha($senha) {
        $this->senha = $senha;
    }
    
    public function setPerfil($perfil) {
        $this->perfil = $perfil;
    }
    
    public function setFoto($foto) {
        $this->foto = $foto;
    }
    
    public function setIdPerfil($id_perfil) {
        $this->id_perfil = $id_perfil;
    }
    
    public function setUnidade($unidade) {
        $this->unidade = $unidade;
    }
    
    /**
     * Converte o objeto para array
     */
    public function toArray() {
        return [
            'id_usuario' => $this->id_usuario,
            'nome' => $this->nome,
            'email' => $this->email,
            'perfil' => $this->perfil,
            'foto' => $this->foto,
            'id_perfil' => $this->id_perfil,
            'unidade' => $this->unidade
        ];
    }
    
    /**
     * Cria um objeto a partir de um array
     */
    public static function fromArray($data) {
        return new Usuario(
            $data['id_usuario'] ?? null,
            $data['nome'] ?? null,
            $data['email'] ?? null,
            $data['senha'] ?? null,
            $data['perfil'] ?? null,
            $data['foto'] ?? null,
            $data['id_perfil'] ?? null,
            $data['unidade'] ?? null
        );
    }
}

