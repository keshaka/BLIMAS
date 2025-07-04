<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get the limit parameter (default to 1 for latest data)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1;
    $limit = max(1, min(100, $limit)); // Ensure limit is between 1 and 100
    
    // Fetch the latest sensor data (excluding battery level for public access)
    $sql = "SELECT 
                id,
                timestamp,
                COALESCE(air_temperature, air_temp) as air_temperature,
                humidity,
                water_level,
                COALESCE(water_temp_depth1, water_temp1) as water_temp_depth1,
                COALESCE(water_temp_depth2, water_temp2) as water_temp_depth2,
                COALESCE(water_temp_depth3, water_temp3) as water_temp_depth3
            FROM sensor_data 
            ORDER BY timestamp DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$limit]);
    $data = $stmt->fetchAll();
    
    if ($data) {
        // Format the data
        $formatted_data = [];
        foreach ($data as $row) {
            $formatted_data[] = [
                'id' => (int)$row['id'],
                'timestamp' => $row['timestamp'],
                'air_temperature' => $row['air_temperature'] ? (float)$row['air_temperature'] : null,
                'humidity' => $row['humidity'] ? (float)$row['humidity'] : null,
                'water_level' => $row['water_level'] ? (float)$row['water_level'] : null,
                'water_temperatures' => [
                    'depth1' => $row['water_temp_depth1'] ? (float)$row['water_temp_depth1'] : null,
                    'depth2' => $row['water_temp_depth2'] ? (float)$row['water_temp_depth2'] : null,
                    'depth3' => $row['water_temp_depth3'] ? (float)$row['water_temp_depth3'] : null
                ]
            ];
        }
        
        $response = [
            'success' => true,
            'data' => $limit === 1 ? $formatted_data[0] : $formatted_data,
            'count' => count($formatted_data),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } else {
        $response = [
            'success' => false,
            'error' => 'No sensor data found',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Sensor data API error: " . $e->getMessage());
    
    $response = [
        'success' => false,
        'error' => 'Internal server error',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    http_response_code(500);
    echo json_encode($response, JSON_PRETTY_PRINT);
}
?>