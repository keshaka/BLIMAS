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

$hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;

try {
    // Get water temperature data for all depths
    $query = "SELECT timestamp, water_temp_depth1, water_temp_depth2, water_temp_depth3 
              FROM sensor_data 
              WHERE timestamp >= datetime('now', '-' || ? || ' hours') 
              ORDER BY timestamp ASC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $hours);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get latest values for current display
    $latest_query = "SELECT water_temp_depth1, water_temp_depth2, water_temp_depth3 
                     FROM sensor_data 
                     ORDER BY timestamp DESC LIMIT 1";
    $latest_stmt = $db->prepare($latest_query);
    $latest_stmt->execute();
    $latest_data = $latest_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'latest' => $latest_data,
        'debug' => [
            'hours_requested' => $hours,
            'records_found' => count($data),
            'database_type' => $db->getAttribute(PDO::ATTR_DRIVER_NAME),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
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