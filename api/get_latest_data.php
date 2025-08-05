<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    $query = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        // Ensure all numeric values are properly formatted
        $response = [
            'id' => $row['id'],
            'air_temperature' => floatval($row['air_temperature']),
            'humidity' => floatval($row['humidity']),
            'water_level' => floatval($row['water_level']),
            'water_temp_depth1' => floatval($row['water_temp_depth1']),
            'water_temp_depth2' => floatval($row['water_temp_depth2']),
            'water_temp_depth3' => floatval($row['water_temp_depth3']),
            'timestamp' => $row['timestamp']
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'No data found']);
    }
} catch(Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>