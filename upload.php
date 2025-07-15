<?php
// Database credentials
$host = "localhost";
$user = "root";
$password = "Qwer3552";
$database = "blimas_db";

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data and sanitize
$water_temp1 = isset($_POST['water_temp1']) ? floatval($_POST['water_temp1']) : null;
$water_temp2 = isset($_POST['water_temp2']) ? floatval($_POST['water_temp2']) : null;
$water_temp3 = isset($_POST['water_temp3']) ? floatval($_POST['water_temp3']) : null;
$humidity     = isset($_POST['humidity'])     ? floatval($_POST['humidity'])     : null;
$air_temp     = isset($_POST['air_temp'])     ? floatval($_POST['air_temp'])     : null;
$water_level  = isset($_POST['water_level'])  ? floatval($_POST['water_level'])  : null;
$battery_level = isset($_POST['battery_level']) ? intval($_POST['battery_level']) : null;
$rssi = isset($_POST['rssi']) ? intval($_POST['rssi']) : null;

// Validate required fields
if ($water_temp1 === null || $water_temp2 === null || $water_temp3 === null ||
    $humidity === null || $air_temp === null || $water_level === null || 
    $battery_level === null || $rssi === null) {
    http_response_code(400);
    echo "Missing required fields.";
    exit;
}

// Insert into sensor_data table
$sensor_sql = "INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3) 
               VALUES (?, ?, ?, ?, ?, ?)";
$sensor_stmt = $conn->prepare($sensor_sql);
$sensor_stmt->bind_param("dddddd", $air_temp, $humidity, $water_level, $water_temp1, $water_temp2, $water_temp3);
$sensor_stmt->execute();

// Insert into battery_status table
$battery_sql = "INSERT INTO battery_status (battery_percentage, rssi) VALUES (?, ?)";
$battery_stmt = $conn->prepare($battery_sql);
$battery_stmt->bind_param("ii", $battery_level, $rssi);
$battery_stmt->execute();

// Success
http_response_code(200);
echo "Data inserted successfully.";

$conn->close();
?>
