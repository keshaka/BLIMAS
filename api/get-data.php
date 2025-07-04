<?php
/**
 * API endpoint to get latest sensor data
 * BLIMAS - Bolgoda Lake Information Monitoring & Analysis System
 */

require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    $database = new Database();
    $pdo = $database->connect();
    
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Get latest sensor data
    $stmt = $pdo->prepare("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute();
    $latest_data = $stmt->fetch();
    
    if ($latest_data) {
        // Format the response
        $response = [
            'success' => true,
            'data' => [
                'water_temp1' => (float)$latest_data['water_temp1'],
                'water_temp2' => (float)$latest_data['water_temp2'],
                'water_temp3' => (float)$latest_data['water_temp3'],
                'air_temp' => (float)$latest_data['air_temp'],
                'humidity' => (float)$latest_data['humidity'],
                'water_level' => (float)$latest_data['water_level'],
                'battery_level' => (float)$latest_data['battery_level'],
                'timestamp' => $latest_data['timestamp']
            ],
            'last_updated' => date('Y-m-d H:i:s')
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'No data available',
            'data' => null
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>