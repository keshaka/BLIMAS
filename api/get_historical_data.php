<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$type = isset($_GET['type']) ? $_GET['type'] : 'temperature';
$hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;

try {
    $query = "SELECT timestamp, ";
    
    switch($type) {
        case 'temperature':
            $query .= "air_temperature as value";
            break;
        case 'humidity':
            $query .= "humidity as value";
            break;
        case 'water_level':
            $query .= "water_level as value";
            break;
        case 'water_temperature':
            $query .= "water_temp_depth1, water_temp_depth2, water_temp_depth3";
            break;
        default:
            $query .= "air_temperature as value";
    }
    
    $query .= " FROM sensor_data WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR) ORDER BY timestamp ASC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $hours);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>