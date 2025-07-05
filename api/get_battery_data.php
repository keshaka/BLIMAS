<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Get latest battery data
    $query = "SELECT * FROM battery_status ORDER BY timestamp DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $latest_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($latest_data) {
        echo json_encode([
            'status' => 'success',
            'data' => $latest_data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No battery data found'
        ]);
    }
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>