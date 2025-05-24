<?php
$host = '';
$db = '';
$user = '';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed");
}

$sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "air_temp" => $row["air_temp"],
        "water_temp1" => $row["water_temp1"],
        "water_temp2" => $row["water_temp2"],
        "water_temp3" => $row["water_temp3"],
        "humidity" => $row["humidity"],
        "water_level" => $row["water_level"],
        "battery_level" => $row["battery_level"]
    ]);
} else {
    echo json_encode(["error" => "No data"]);
}

$conn->close();
?>
