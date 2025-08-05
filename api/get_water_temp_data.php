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

$period = $_GET['period'] ?? 'day';

$whereClause = '';
switch($period) {
    case 'day':
        $whereClause = "WHERE DATE(timestamp) = CURDATE()";
        break;
    case 'week':
        $whereClause = "WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $whereClause = "WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    default:
        $whereClause = "WHERE DATE(timestamp) = CURDATE()";
}

try {
    $query = "SELECT timestamp, water_temp_depth1, water_temp_depth2, water_temp_depth3 FROM sensor_data $whereClause ORDER BY timestamp ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            'timestamp' => $row['timestamp'],
            'water_temp_depth1' => floatval($row['water_temp_depth1']),
            'water_temp_depth2' => floatval($row['water_temp_depth2']),
            'water_temp_depth3' => floatval($row['water_temp_depth3'])
        ];
    }
    
    echo json_encode($data);
} catch(Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>