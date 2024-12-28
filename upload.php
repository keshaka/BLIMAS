<?php
// Collect POST data
$temp1 = $_POST['temp1'];
$temp2 = $_POST['temp2'];
$temp3 = $_POST['temp3'];
$humidity = $_POST['humidity'];
$tempDHT = $_POST['tempDHT'];
$distance = $_POST['distance'];

// Save to file
$file = 'sensor_data.txt';
$data = "Temp1: $temp1, Temp2: $temp2, Temp3: $temp3, Humidity: $humidity, TempDHT: $tempDHT, Distance: $distance\n";
file_put_contents($file, $data, FILE_APPEND);

// Response
echo "Data received successfully!";
?>