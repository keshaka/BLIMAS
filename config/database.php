<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'blimas_db';
    private $username = 'root';
    private $password = '';
    private $db_file = 'blimas.db';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Try SQLite first for development
            if ($this->useSQLite()) {
                $db_path = dirname(__DIR__) . '/' . $this->db_file;
                $this->conn = new PDO("sqlite:" . $db_path);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->createTables();
            } else {
                // Fallback to MySQL
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->exec("set names utf8");
            }
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
    
    private function useSQLite() {
        // Use SQLite if MySQL is not available or in development
        return !extension_loaded('mysql') || getenv('USE_SQLITE') === '1' || !$this->isMySQLAvailable();
    }
    
    private function isMySQLAvailable() {
        try {
            $test_conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    private function createTables() {
        $schema = "
        CREATE TABLE IF NOT EXISTS sensor_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            air_temperature REAL,
            humidity REAL,
            water_level REAL,
            water_temp_depth1 REAL,
            water_temp_depth2 REAL,
            water_temp_depth3 REAL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS battery_status (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            battery_percentage INTEGER,
            is_charging INTEGER,
            rssi INTEGER
        );
        ";
        
        $this->conn->exec($schema);
    }
}
?>