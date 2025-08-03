<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$database = new Database();
$db = $database->getConnection();

try {
    // Get latest sensor data
    $query = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $latest_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($latest_data) {
        // Add debug information
        $response = [
            'status' => 'success',
            'data' => $latest_data,
            'timestamp' => date('Y-m-d H:i:s'),
            'debug' => [
                'database_type' => $db->getAttribute(PDO::ATTR_DRIVER_NAME),
                'record_count' => 1
            ]
        ];
        echo json_encode($response);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No data found',
            'debug' => [
                'database_type' => $db->getAttribute(PDO::ATTR_DRIVER_NAME),
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'file' => __FILE__
        ]
    ]);
}
?>