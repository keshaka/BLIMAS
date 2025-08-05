<?php
// Database credentials
$host = 'localhost';
$db   = 'blimas_db';
$user = 'root';
$pass = 'Qwer3552';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get the latest sensor data
$sql = "SELECT air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3 
        FROM sensor_data 
        ORDER BY timestamp DESC 
        LIMIT 1";

$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    // Build response
    $response = [
        'air_temp' => floatval($row['air_temperature']),
        'humidity' => floatval($row['humidity']),
        'water_level' => floatval($row['water_level']),
        'water_temp1' => floatval($row['water_temp_depth1']),
        'water_temp2' => floatval($row['water_temp_depth2']),
        'water_temp3' => floatval($row['water_temp_depth3'])
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'No data found']);
}

$conn->close();
?>