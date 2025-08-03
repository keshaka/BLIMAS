<?php
/**
 * Sample data generator for BLIMAS system
 * Creates realistic sensor data for testing dashboard and charts
 */

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed\n");
}

// Generate realistic sensor data
function generateSensorData() {
    $base_time = time();
    $data = [];
    
    for ($i = 0; $i < 100; $i++) {
        $timestamp = date('Y-m-d H:i:s', $base_time - ($i * 300)); // Every 5 minutes
        
        // Simulate daily temperature variation
        $hour = intval(date('H', $base_time - ($i * 300)));
        $temp_base = 26 + (5 * sin(($hour - 6) * pi() / 12)); // Temperature varies through day
        
        // Add some random variation
        $temp_variation = (rand(-20, 20) / 10);
        $humidity_variation = (rand(-150, 150) / 10);
        $water_level_variation = (rand(-30, 30) / 100);
        
        $data[] = [
            'air_temperature' => round($temp_base + $temp_variation, 1),
            'humidity' => round(65 + $humidity_variation, 1),
            'water_level' => round(2.3 + $water_level_variation, 2),
            'water_temp_1' => round($temp_base - 1 + (rand(-10, 10) / 10), 1),
            'water_temp_2' => round($temp_base - 2 + (rand(-10, 10) / 10), 1),
            'water_temp_3' => round($temp_base - 3 + (rand(-10, 10) / 10), 1),
            'timestamp' => $timestamp
        ];
    }
    
    return array_reverse($data); // Oldest first
}

try {
    // Clear existing data
    $db->exec("DELETE FROM sensor_data");
    echo "Cleared existing sensor data\n";
    
    // Insert simulated data
    $data = generateSensorData();
    $query = "INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    $inserted = 0;
    foreach ($data as $row) {
        $result = $stmt->execute([
            $row['air_temperature'],
            $row['humidity'],
            $row['water_level'],
            $row['water_temp_1'],
            $row['water_temp_2'],
            $row['water_temp_3'],
            $row['timestamp']
        ]);
        if ($result) $inserted++;
    }
    
    echo "Successfully inserted $inserted records out of " . count($data) . " generated records\n";
    
    // Display latest record to verify
    $verify_query = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
    $verify_stmt = $db->prepare($verify_query);
    $verify_stmt->execute();
    $latest = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($latest) {
        echo "\nLatest record created:\n";
        echo "Temperature: {$latest['air_temperature']}°C\n";
        echo "Humidity: {$latest['humidity']}%\n";
        echo "Water Level: {$latest['water_level']}m\n";
        echo "Water Temps: {$latest['water_temp_depth1']}°C, {$latest['water_temp_depth2']}°C, {$latest['water_temp_depth3']}°C\n";
        echo "Timestamp: {$latest['timestamp']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>