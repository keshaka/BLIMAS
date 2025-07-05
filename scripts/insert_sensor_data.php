<?php
/**
 * Script to insert sensor data into BLIMAS database
 * This can be called by your sensor hardware or data collection system
 */

include_once '../config/database.php';

// Get POST data or use GET for testing
$air_temperature = isset($_POST['air_temp']) ? floatval($_POST['air_temp']) : (isset($_GET['air_temp']) ? floatval($_GET['air_temp']) : null);
$humidity = isset($_POST['humidity']) ? floatval($_POST['humidity']) : (isset($_GET['humidity']) ? floatval($_GET['humidity']) : null);
$water_level = isset($_POST['water_level']) ? floatval($_POST['water_level']) : (isset($_GET['water_level']) ? floatval($_GET['water_level']) : null);
$water_temp_1 = isset($_POST['water_temp_1']) ? floatval($_POST['water_temp_1']) : (isset($_GET['water_temp_1']) ? floatval($_GET['water_temp_1']) : null);
$water_temp_2 = isset($_POST['water_temp_2']) ? floatval($_POST['water_temp_2']) : (isset($_GET['water_temp_2']) ? floatval($_GET['water_temp_2']) : null);
$water_temp_3 = isset($_POST['water_temp_3']) ? floatval($_POST['water_temp_3']) : (isset($_GET['water_temp_3']) ? floatval($_GET['water_temp_3']) : null);

// API Key validation (optional but recommended)
$api_key = isset($_POST['api_key']) ? $_POST['api_key'] : (isset($_GET['api_key']) ? $_GET['api_key'] : '');
$valid_api_key = 'your_sensor_api_key_here'; // Change this to a secure key

if ($api_key !== $valid_api_key) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API key']);
    exit;
}

// Validate required fields
if ($air_temperature === null || $humidity === null || $water_level === null || 
    $water_temp_1 === null || $water_temp_2 === null || $water_temp_3 === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required sensor data']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$air_temperature, $humidity, $water_level, $water_temp_1, $water_temp_2, $water_temp_3])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Sensor data inserted successfully',
            'id' => $db->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert data']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>