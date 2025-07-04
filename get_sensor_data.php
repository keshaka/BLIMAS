<?php
/**
 * BLIMAS Sensor Data Endpoint
 * Updated to use centralized database configuration
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once 'includes/database.php';

try {
    $conn = DatabaseConfig::getMySQLiConnection();
    
    // Fetch the latest sensor data
    $sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Format the response to match expected format
        $response = [
            "success" => true,
            "data" => [
                "air_temperature" => floatval($data['air_temperature']),
                "humidity" => floatval($data['humidity']),
                "water_level" => floatval($data['water_level']),
                "water_temp1" => floatval($data['water_temp1']),
                "water_temp2" => floatval($data['water_temp2']),
                "water_temp3" => floatval($data['water_temp3']),
                "battery_level" => $data['battery_level'] ? floatval($data['battery_level']) : null,
                "timestamp" => $data['timestamp'],
                "id" => $data['id']
            ]
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "No sensor data found"
        ]);
    }

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
}
?>
