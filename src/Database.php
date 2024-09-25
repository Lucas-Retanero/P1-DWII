<?php
namespace Api\P1Dwii;
class Database {
    private $pdo;

    public function __construct() {
        $this->pdo = new \PDO('sqlite:' . __DIR__ . '/db/produtos.db');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection() {
        return $this->pdo;
    }
}
