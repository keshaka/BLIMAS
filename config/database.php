<?php
/**
 * Database configuration for BLIMAS
 * Bolgoda Lake Information Monitoring & Analysis System
 */

class Database {
    private $host = 'database-1.c3e8ygu8uk3t.eu-north-1.rds.amazonaws.com';
    private $db_name = 'blimas';
    private $username = 'admin';
    private $password = 'kakkabetta123';
    private $charset = 'utf8mb4';
    public $pdo;

    public function connect() {
        $this->pdo = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        
        return $this->pdo;
    }
}
?>