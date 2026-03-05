<?php
/**
 * Classe base Model - acesso ao banco via PDO
 */
abstract class Model {
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }
}
