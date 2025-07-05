<?php
/**
 * System monitoring script to check BLIMAS health
 * Run this via cron job every 5 minutes
 */

include_once '../config/database.php';

$log_file = '../logs/system_monitor.log';

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Check database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if we have recent data (within last 10 minutes)
    $query = "SELECT COUNT(*) as count FROM sensor_data WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        logMessage("WARNING: No sensor data received in the last 10 minutes");
        // Send alert email or notification here
    } else {
        logMessage("INFO: System healthy - recent data available");
    }
    
    // Check for sensor anomalies
    $query = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($latest) {
        $alerts = [];
        
        // Temperature checks
        if ($latest['air_temperature'] < -10 || $latest['air_temperature'] > 50) {
            $alerts[] = "Extreme air temperature: " . $latest['air_temperature'] . "°C";
        }
        
        // Humidity checks
        if ($latest['humidity'] < 0 || $latest['humidity'] > 100) {
            $alerts[] = "Invalid humidity reading: " . $latest['humidity'] . "%";
        }
        
        // Water level checks
        if ($latest['water_level'] < 0 || $latest['water_level'] > 10) {
            $alerts[] = "Extreme water level: " . $latest['water_level'] . "m";
        }
        
        // Water temperature checks
        foreach (['water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3'] as $field) {
            if ($latest[$field] < 0 || $latest[$field] > 40) {
                $alerts[] = "Extreme water temperature at $field: " . $latest[$field] . "°C";
            }
        }
        
        if (!empty($alerts)) {
            foreach ($alerts as $alert) {
                logMessage("ALERT: $alert");
            }
        }
    }
    
} catch (Exception $e) {
    logMessage("ERROR: Database error - " . $e->getMessage());
}

// Clean old logs (keep last 30 days)
if (file_exists($log_file)) {
    $logs = file($log_file);
    $cleaned_logs = [];
    $cutoff_date = date('Y-m-d', strtotime('-30 days'));
    
    foreach ($logs as $log) {
        if (strpos($log, $cutoff_date) !== false || strpos($log, date('Y-m-d')) !== false) {
            $cleaned_logs[] = $log;
        }
    }
    
    file_put_contents($log_file, implode('', $cleaned_logs));
}
?>