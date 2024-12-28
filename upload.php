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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $temp1 = $_POST['temp1'] ?? 'NaN';
    $temp2 = $_POST['temp2'] ?? 'NaN';
    $temp3 = $_POST['temp3'] ?? 'NaN';
    $humidity = $_POST['humidity'] ?? 'NaN';
    $tempDHT = $_POST['tempDHT'] ?? 'NaN';
    $distance = $_POST['distance'] ?? 'NaN';

    // Write data to RDS
    $stmt = $conn->prepare("INSERT INTO sensor_data (temp1, temp2, temp3, humidity, tempDHT, distance) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("dddddd", $temp1, $temp2, $temp3, $humidity, $tempDHT, $distance);
    
    if ($stmt->execute()) {
        echo "Data received and stored!";
    } else {
        echo "Failed to store data: " . $stmt->error;
    }

    $stmt->close();

    // Write data to a file as a backup
    $file = '/var/www/html/sensor_data.txt'; // Adjust path if needed
    $data = "Temp1: $temp1, Temp2: $temp2, Temp3: $temp3, Humidity: $humidity, TempDHT: $tempDHT, Distance: $distance\n";
    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
} else {
    echo "Invalid request method.";
}

$conn->close();
?>