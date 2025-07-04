<?php
/**
 * BLIMAS Database Configuration
 * Bolgoda Lake Information Monitoring & Analysis System
 * 
 * Centralized database configuration for all components
 */

class DatabaseConfig {
    // Database connection parameters
    private static $config = [
        'host' => 'localhost',  // Change this to your database host
        'username' => '',       // Change this to your database username
        'password' => '',       // Change this to your database password
        'database' => 'blimas_db',
        'charset' => 'utf8mb4',
        'port' => 3306
    ];
    
    // For production, you might want to use environment variables
    // Example: self::$config['host'] = $_ENV['DB_HOST'] ?? 'localhost';
    
    /**
     * Get database configuration
     */
    public static function getConfig() {
        return self::$config;
    }
    
    /**
     * Get MySQLi connection
     */
    public static function getMySQLiConnection() {
        $config = self::$config;
        
        $conn = new mysqli(
            $config['host'],
            $config['username'], 
            $config['password'],
            $config['database'],
            $config['port']
        );
        
        if ($conn->connect_error) {
            error_log("BLIMAS Database Connection Failed: " . $conn->connect_error);
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset($config['charset']);
        return $conn;
    }
    
    /**
     * Get PDO connection
     */
    public static function getPDOConnection() {
        $config = self::$config;
        
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            return new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            error_log("BLIMAS PDO Connection Failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Test database connection
     */
    public static function testConnection() {
        try {
            $conn = self::getMySQLiConnection();
            $result = $conn->query("SELECT 1");
            $conn->close();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get system configuration from database
     */
    public static function getSystemConfig($key = null) {
        try {
            $conn = self::getMySQLiConnection();
            
            if ($key) {
                $stmt = $conn->prepare("SELECT config_value FROM system_config WHERE config_key = ? AND is_active = 1");
                $stmt->bind_param("s", $key);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $conn->close();
                return $row ? $row['config_value'] : null;
            } else {
                $result = $conn->query("SELECT config_key, config_value FROM system_config WHERE is_active = 1");
                $config = [];
                while ($row = $result->fetch_assoc()) {
                    $config[$row['config_key']] = $row['config_value'];
                }
                $conn->close();
                return $config;
            }
        } catch (Exception $e) {
            error_log("BLIMAS Config Error: " . $e->getMessage());
            return null;
        }
    }
}

// For backward compatibility with existing code
function getLegacyConnection() {
    return DatabaseConfig::getMySQLiConnection();
}
?>