<?php
/**
 * Alert System API
 * BLIMAS - Bolgoda Lake Information Monitoring & Analysis System
 */

require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

class AlertSystem {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function checkAlerts() {
        // Get latest sensor data
        $stmt = $this->pdo->prepare("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1");
        $stmt->execute();
        $latest_data = $stmt->fetch();
        
        if (!$latest_data) {
            return ['alerts' => [], 'message' => 'No sensor data available'];
        }
        
        $alerts = [];
        
        // Check temperature alerts
        if ($latest_data['air_temp'] > TEMP_HIGH_THRESHOLD) {
            $alerts[] = $this->createAlert('temperature', 'high', 'Air temperature is critically high', $latest_data['air_temp'], TEMP_HIGH_THRESHOLD);
        } elseif ($latest_data['air_temp'] < TEMP_LOW_THRESHOLD) {
            $alerts[] = $this->createAlert('temperature', 'high', 'Air temperature is critically low', $latest_data['air_temp'], TEMP_LOW_THRESHOLD);
        }
        
        // Check humidity alerts
        if ($latest_data['humidity'] > HUMIDITY_HIGH_THRESHOLD) {
            $alerts[] = $this->createAlert('humidity', 'medium', 'Humidity level is very high', $latest_data['humidity'], HUMIDITY_HIGH_THRESHOLD);
        } elseif ($latest_data['humidity'] < HUMIDITY_LOW_THRESHOLD) {
            $alerts[] = $this->createAlert('humidity', 'medium', 'Humidity level is very low', $latest_data['humidity'], HUMIDITY_LOW_THRESHOLD);
        }
        
        // Check water level alerts
        if ($latest_data['water_level'] > WATER_LEVEL_HIGH_THRESHOLD) {
            $alerts[] = $this->createAlert('water_level', 'high', 'Water level is dangerously high', $latest_data['water_level'], WATER_LEVEL_HIGH_THRESHOLD);
        } elseif ($latest_data['water_level'] < WATER_LEVEL_LOW_THRESHOLD) {
            $alerts[] = $this->createAlert('water_level', 'critical', 'Water level is critically low', $latest_data['water_level'], WATER_LEVEL_LOW_THRESHOLD);
        }
        
        // Check battery alerts
        if ($latest_data['battery_level'] < 20) {
            $alerts[] = $this->createAlert('battery', 'critical', 'Battery level is critically low', $latest_data['battery_level'], 20);
        } elseif ($latest_data['battery_level'] < 40) {
            $alerts[] = $this->createAlert('battery', 'medium', 'Battery level is low', $latest_data['battery_level'], 40);
        }
        
        // Check for stale data (no updates in last 10 minutes)
        $last_update = strtotime($latest_data['timestamp']);
        $current_time = time();
        if (($current_time - $last_update) > 600) { // 10 minutes
            $alerts[] = $this->createAlert('system', 'high', 'Sensor data is stale - no updates in ' . round(($current_time - $last_update) / 60) . ' minutes', null, null);
        }
        
        // Save alerts to database
        foreach ($alerts as $alert) {
            $this->saveAlert($alert);
        }
        
        return [
            'success' => true,
            'alerts' => $alerts,
            'latest_data' => $latest_data,
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function createAlert($type, $severity, $message, $value = null, $threshold = null) {
        return [
            'alert_type' => $type,
            'severity' => $severity,
            'message' => $message,
            'value' => $value,
            'threshold_value' => $threshold,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function saveAlert($alert) {
        try {
            // Check if similar alert already exists and is active
            $stmt = $this->pdo->prepare("
                SELECT id FROM alerts 
                WHERE alert_type = ? AND severity = ? AND is_active = 1 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$alert['alert_type'], $alert['severity']]);
            
            if ($stmt->rowCount() == 0) {
                // Insert new alert
                $stmt = $this->pdo->prepare("
                    INSERT INTO alerts (alert_type, severity, message, value, threshold_value, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, 1, NOW())
                ");
                $stmt->execute([
                    $alert['alert_type'],
                    $alert['severity'],
                    $alert['message'],
                    $alert['value'],
                    $alert['threshold_value']
                ]);
            }
        } catch (Exception $e) {
            error_log("Error saving alert: " . $e->getMessage());
        }
    }
    
    public function getActiveAlerts() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM alerts 
                WHERE is_active = 1 
                ORDER BY severity DESC, created_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function acknowledgeAlert($alert_id, $user = 'System') {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE alerts 
                SET acknowledged = 1, acknowledged_by = ?, acknowledged_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$user, $alert_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

try {
    $database = new Database();
    $pdo = $database->connect();
    
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    $alertSystem = new AlertSystem($pdo);
    $action = $_GET['action'] ?? 'check';
    
    switch ($action) {
        case 'check':
            $result = $alertSystem->checkAlerts();
            break;
        case 'active':
            $result = [
                'success' => true,
                'alerts' => $alertSystem->getActiveAlerts()
            ];
            break;
        case 'acknowledge':
            $alert_id = (int)($_GET['id'] ?? 0);
            $user = $_GET['user'] ?? 'Anonymous';
            $success = $alertSystem->acknowledgeAlert($alert_id, $user);
            $result = [
                'success' => $success,
                'message' => $success ? 'Alert acknowledged' : 'Failed to acknowledge alert'
            ];
            break;
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Alert system error: ' . $e->getMessage()
    ]);
}
?>