<?php
class Database {
    private $connection;
    private $db_path;
    
    public function __construct() {
        $this->db_path = __DIR__ . '/../database/map_management.db';
        $this->connect();
    }
    
    private function connect() {
        try {
            // Create database directory if it doesn't exist
            $db_dir = dirname($this->db_path);
            if (!file_exists($db_dir)) {
                mkdir($db_dir, 0755, true);
            }
            
            $this->connection = new PDO('sqlite:' . $this->db_path);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database query error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}
?>
