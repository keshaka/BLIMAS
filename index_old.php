<?php
$servername = ""; // Replace with your RDS endpoint
$username = "";                // RDS username
$password = "";                // RDS password
$dbname = "sensor_data";                 // Your database name

// Connect to RDS database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the latest data
$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Temp1: " . $row['temp1'] . " °C<br>";
    echo "Temp2: " . $row['temp2'] . " °C<br>";
    echo "Temp3: " . $row['temp3'] . " °C<br>";
    echo "Humidity: " . $row['humidity'] . " %<br>";
    echo "TempDHT: " . $row['tempDHT'] . " °C<br>";
    echo "Distance: " . $row['distance'] . " cm<br>";
} else {
    echo "No data found!";
}

$conn->close();
?>