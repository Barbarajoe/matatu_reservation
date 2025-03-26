<?php
class DBController {
    private $host = "localhost";
    private $user = "app_user";
    private $password = "StrongPassword123!";
    private $database = "matatu_reservation";
    private $conn;
    
    public function __construct() {
        $this->conn = $this->connectDB();
    }
    
    private function connectDB() {
        try {
            $conn = new PDO(
                "mysql:host=$this->host;dbname=$this->database;charset=utf8mb4",
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            return $conn;
        } catch(PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            die("System maintenance in progress. Please try again later.");
        }
    }

    public function runQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return false;
        }
    }
}
?>