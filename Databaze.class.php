<?php

declare(strict_types=1);


class Databaze {
    private String $dbDsn = "mysql:host=localhost;dbname=pro_sportovce;charset=utf8";
    private String $dbName = "root";
    private String $dbPassword = ""; 

    private PDO $pdo;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        try {
            $this->pdo = new PDO($this->dbDsn,$this->dbName,$this->dbPassword);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function __call($method, $args)
    {
        if (method_exists($this->pdo, $method)) {
            return call_user_func_array([$this->pdo, $method], $args);
        } else {
            throw new BadMethodCallException("Method $method does not exist.");
        }
    }

}