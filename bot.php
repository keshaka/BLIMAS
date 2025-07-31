<?php
$host = "localhost";
$db = "blimas_db";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Latest sensor data
$sensor_sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$sensor_result = $conn->query($sensor_sql);
$sensor_data = $sensor_result->fetch_assoc();

// Latest battery status
$battery_sql = "SELECT * FROM battery_status ORDER BY timestamp DESC LIMIT 1";
$battery_result = $conn->query($battery_sql);
$battery_data = $battery_result->fetch_assoc();

$response = [
    "sensor" => $sensor_data,
    "battery" => $battery_data
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
