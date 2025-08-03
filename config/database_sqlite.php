<?php
class Database {
    private $db_file = 'blimas.db';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $db_path = dirname(__DIR__) . '/' . $this->db_file;
            $this->conn = new PDO("sqlite:" . $db_path);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if they don't exist
            $this->createTables();
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
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