<?php
/**
 * Data simulator for testing BLIMAS system
 * Generates realistic sensor data for development/testing
 */

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Generate realistic sensor data
function generateSensorData() {
    $base_time = time();
    $data = [];
    
    for ($i = 0; $i < 100; $i++) {
        $timestamp = date('Y-m-d H:i:s', $base_time - ($i * 300)); // Every 5 minutes
        
        // Simulate daily temperature variation
        $hour = date('H', $base_time - ($i * 300));
        $temp_base = 20 + (10 * sin(($hour - 6) * pi() / 12)); // Temperature varies through day
        
        $data[] = [
            'air_temperature' => round($temp_base + rand(-20, 20) / 10, 1),
            'humidity' => round(60 + rand(-150, 150) / 10, 1),
            'water_level' => round(2.5 + rand(-30, 30) / 100, 2),
            'water_temp_1' => round($temp_base - 2 + rand(-10, 10) / 10, 1),
            'water_temp_2' => round($temp_base - 3 + rand(-10, 10) / 10, 1),
            'water_temp_3' => round($temp_base - 4 + rand(-10, 10) / 10, 1),
            'timestamp' => $timestamp
        ];
    }
    
    return array_reverse($data); // Oldest first
}

try {
    // Clear existing data
    $db->exec("DELETE FROM sensor_data");
    
    // Insert simulated data
    $data = generateSensorData();
    $query = "INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    foreach ($data as $row) {
        $stmt->execute([
            $row['air_temperature'],
            $row['humidity'],
            $row['water_level'],
            $row['water_temp_1'],
            $row['water_temp_2'],
            $row['water_temp_3'],
            $row['timestamp']
        ]);
    }
    
    echo "Simulated data inserted successfully! " . count($data) . " records created.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>