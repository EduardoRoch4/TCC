<?php
/**
 * Classe de conexão com o banco de dados
 * Implementa o padrão Singleton para garantir uma única instância de conexão
 */
class Database {
    private static $instance = null;
    private $connection;
    
    // Configurações do banco de dados
    private $host = "localhost";
    private $user = "root";
    private $pass = "senaisp";
    private $db = "Techfit";
    
    /**
     * Construtor privado para impedir instanciação direta
     */
    private function __construct() {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->db);
        
        if ($this->connection->connect_error) {
            die("Erro na conexão com o banco: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset("utf8");
    }
    
    /**
     * Retorna a instância única da conexão (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Retorna a conexão mysqli
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne deserialização da instância
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

