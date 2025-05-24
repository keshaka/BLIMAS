<?php
// Database credentials
$host = '';
$db = '';
$user = '';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Connect to DB
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Check if required data is present
    if (
        isset($_POST['water_temp1']) &&
        isset($_POST['water_temp2']) &&
        isset($_POST['water_temp3']) &&
        isset($_POST['humidity']) &&
        isset($_POST['air_temp']) &&
        isset($_POST['water_level']) &&
        isset($_POST['battery_level'])
    ) {
        // Prepare SQL insert
        $stmt = $pdo->prepare("INSERT INTO sensor_data (water_temp1, water_temp2, water_temp3, humidity, air_temp, water_level, battery_level)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $_POST['water_temp1'],
            $_POST['water_temp2'],
            $_POST['water_temp3'],
            $_POST['humidity'],
            $_POST['air_temp'],
            $_POST['water_level'],
            $_POST['battery_level']
        ]);

        echo "Data inserted successfully.";

        // Create log line
        $timestamp = date("Y-m-d H:i:s");
        $log_line = "$timestamp, T1:{$_POST['water_temp1']}, T2:{$_POST['water_temp2']}, T3:{$_POST['water_temp3']}, AirT:{$_POST['air_temp']}, H:{$_POST['humidity']}, W:{$_POST['water_level']}, B:{$_POST['battery_level']}\n";

        // Append to file
        file_put_contents('sensor_log.txt', $log_line, FILE_APPEND | LOCK_EX);
    } else {
        echo "Missing POST data.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
