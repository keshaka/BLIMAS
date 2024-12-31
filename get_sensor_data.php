<?php
header('Content-Type: application/json');

// Connect to the database
$servername = "your-rds-endpoint.amazonaws.com";
$username = "your_rds_username";
$password = "your_rds_password";
$dbname = "your_database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Fetch the latest sensor data
$sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No data found"]);
}

$conn->close();
?>
