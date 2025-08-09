<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection parameters - UPDATE THESE WITH YOUR ACTUAL VALUES
\$servername = "localhost";  // or your database host
\$username = "root";  // Replace with actual username
\$password = "";  // Replace with actual password
\$dbname = "blimas_db";   // Replace with actual database name

// Return sample data function
function returnSampleData() {
    \$today = date('Y-m-d');
    return [
        'air_temp' => ['avg' => 28.5, 'max' => 32.1, 'min' => 24.8],
        'humidity' => ['avg' => 75.2, 'max' => 89.0, 'min' => 62.5],
        'water_level' => ['avg' => 145.8, 'max' => 148.2, 'min' => 143.1],
        'water_temp1' => ['avg' => 27.8, 'max' => 29.5, 'min' => 26.2],
        'water_temp2' => ['avg' => 26.9, 'max' => 28.1, 'min' => 25.7],
        'water_temp3' => ['avg' => 25.8, 'max' => 26.8, 'min' => 24.9],
        'date' => \$today,
        'record_count' => 50,
        'status' => 'sample_data'
    ];
}

try {
    // Check if database credentials are still default
    if (\$username === "your_actual_db_username" || \$password === "your_actual_db_password") {
        // Return sample data if database not configured
        echo json_encode(returnSampleData());
        exit;
    }

    // Create connection
    \$conn = new PDO("mysql:host=\$servername;dbname=\$dbname", \$username, \$password);
    \$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get today's date
    \$today = date('Y-m-d');
    
    // Check if table exists
    \$tableCheck = \$conn->query("SHOW TABLES LIKE 'sensor_data'");
    if (\$tableCheck->rowCount() == 0) {
        echo json_encode(returnSampleData());
        exit;
    }
    
    \$sql = "SELECT 
                AVG(air_temperature) as air_temp_avg,
                MAX(air_temperature) as air_temp_max,
                MIN(air_temperature) as air_temp_min,
                AVG(humidity) as humidity_avg,
                MAX(humidity) as humidity_max,
                MIN(humidity) as humidity_min,
                AVG(water_level) as water_level_avg,
                MAX(water_level) as water_level_max,
                MIN(water_level) as water_level_min,
                AVG(water_temp_depth1) as water_temp1_avg,
                MAX(water_temp_depth1) as water_temp1_max,
                MIN(water_temp_depth1) as water_temp1_min,
                AVG(water_temp_depth2) as water_temp2_avg,
                MAX(water_temp_depth2) as water_temp2_max,
                MIN(water_temp_depth2) as water_temp2_min,
                AVG(water_temp_depth3) as water_temp3_avg,
                MAX(water_temp_depth3) as water_temp3_max,
                MIN(water_temp_depth3) as water_temp3_min,
                COUNT(*) as record_count
            FROM sensor_data 
            WHERE DATE(timestamp) = ?";
    
    \$stmt = \$conn->prepare(\$sql);
    \$stmt->execute([\$today]);
    \$result = \$stmt->fetch(PDO::FETCH_ASSOC);

    if (\$result && \$result['record_count'] > 0) {
        echo json_encode([
            'air_temp' => [
                'avg' => \$result['air_temp_avg'] ? round(floatval(\$result['air_temp_avg']), 1) : 0,
                'max' => \$result['air_temp_max'] ? round(floatval(\$result['air_temp_max']), 1) : 0,
                'min' => \$result['air_temp_min'] ? round(floatval(\$result['air_temp_min']), 1) : 0
            ],
            'humidity' => [
                'avg' => \$result['humidity_avg'] ? round(floatval(\$result['humidity_avg']), 1) : 0,
                'max' => \$result['humidity_max'] ? round(floatval(\$result['humidity_max']), 1) : 0,
                'min' => \$result['humidity_min'] ? round(floatval(\$result['humidity_min']), 1) : 0
            ],
            'water_level' => [
                'avg' => \$result['water_level_avg'] ? round(floatval(\$result['water_level_avg']), 1) : 0,
                'max' => \$result['water_level_max'] ? round(floatval(\$result['water_level_max']), 1) : 0,
                'min' => \$result['water_level_min'] ? round(floatval(\$result['water_level_min']), 1) : 0
            ],
            'water_temp1' => [
                'avg' => \$result['water_temp1_avg'] ? round(floatval(\$result['water_temp1_avg']), 1) : 0,
                'max' => \$result['water_temp1_max'] ? round(floatval(\$result['water_temp1_max']), 1) : 0,
                'min' => \$result['water_temp1_min'] ? round(floatval(\$result['water_temp1_min']), 1) : 0
            ],
            'water_temp2' => [
                'avg' => \$result['water_temp2_avg'] ? round(floatval(\$result['water_temp2_avg']), 1) : 0,
                'max' => \$result['water_temp2_max'] ? round(floatval(\$result['water_temp2_max']), 1) : 0,
                'min' => \$result['water_temp2_min'] ? round(floatval(\$result['water_temp2_min']), 1) : 0
            ],
            'water_temp3' => [
                'avg' => \$result['water_temp3_avg'] ? round(floatval(\$result['water_temp3_avg']), 1) : 0,
                'max' => \$result['water_temp3_max'] ? round(floatval(\$result['water_temp3_max']), 1) : 0,
                'min' => \$result['water_temp3_min'] ? round(floatval(\$result['water_temp3_min']), 1) : 0
            ],
            'date' => \$today,
            'record_count' => intval(\$result['record_count']),
            'status' => 'success'
        ]);
    } else {
        // Return sample data if no records found for today
        echo json_encode(returnSampleData());
    }

} catch(PDOException \$e) {
    // Return sample data on database error
    echo json_encode(returnSampleData());
}

if (isset(\$conn)) {
    \$conn = null;
}
?>