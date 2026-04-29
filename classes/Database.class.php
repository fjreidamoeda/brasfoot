<?php
if (!class_exists('Database')) {
    class Database {
        private static $instance = null;
        private $conn;
        private $sqlitePath;
        
        private function __construct() {
            $this->sqlitePath = __DIR__ . '/../Banco de dados/streamblack.sqlite';
            $this->conn = new PDO('sqlite:' . $this->sqlitePath);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        public function getConnection() {
            return $this->conn;
        }
        
        public function query($sql, $params = []) {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
        
        public function fetchOne($sql, $params = []) {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        public function fetchAll($sql, $params = []) {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        public function insert($table, $data) {
            $keys = array_keys($data);
            $fields = implode(', ', $keys);
            $placeholders = ':' . implode(', :', $keys);
            $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            $stmt->execute();
            return $this->conn->lastInsertId();
        }
        
        public function update($table, $data, $where, $whereParams = []) {
            $setParts = [];
            foreach ($data as $key => $value) {
                $setParts[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $setParts);
            $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            foreach ($whereParams as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            return $stmt->execute();
        }
        
        public function delete($table, $where, $whereParams = []) {
            $sql = "DELETE FROM {$table} WHERE {$where}";
            $stmt = $this->conn->prepare($sql);
            foreach ($whereParams as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            return $stmt->execute();
        }
        
        public function createSchema() {
            $schemaPath = __DIR__ . '/../database/schema.sql';
            if (!file_exists($schemaPath)) {
                return false;
            }
            $sql = file_get_contents($schemaPath);
            $this->conn->exec($sql);
            return true;
        }
    }
}
