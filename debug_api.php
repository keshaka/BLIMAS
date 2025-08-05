<?php
// Debug API endpoints to ensure they're working
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>BLIMAS API Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .button { background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin: 5px; display: inline-block; }
    </style>
</head>
<body>
    <h1>BLIMAS API Debug Tool</h1>
    
    <h2>Test Links</h2>
    <a href="?test=latest" class="button">Test Latest Data</a>
    <a href="?test=air_temp" class="button">Test Air Temperature</a>
    <a href="?test=humidity" class="button">Test Humidity</a>
    <a href="?test=water_level" class="button">Test Water Level</a>
    <a href="?test=water_temp" class="button">Test Water Temperature</a>
    
    <?php
    if (isset($_GET['test'])) {
        $test = $_GET['test'];
        
        echo "<h2>Testing: " . ucwords(str_replace('_', ' ', $test)) . "</h2>";
        
        $apiUrls = [
            'latest' => 'api/get_latest_data.php',
            'air_temp' => 'api/get_historical_data.php?type=air_temperature&period=day',
            'humidity' => 'api/get_historical_data.php?type=humidity&period=day',
            'water_level' => 'api/get_historical_data.php?type=water_level&period=day',
            'water_temp' => 'api/get_water_temp_data.php?period=day'
        ];
        
        if (isset($apiUrls[$test])) {
            $url = $apiUrls[$test];
            
            echo "<div class='test-section'>";
            echo "<h3>Testing URL: $url</h3>";
            
            // Test if file exists
            if (file_exists($url)) {
                echo "<p style='color: green;'>✓ File exists</p>";
                
                // Test API call
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => 'Content-Type: application/json',
                        'timeout' => 10
                    ]
                ]);
                
                $result = @file_get_contents($url, false, $context);
                
                if ($result !== false) {
                    echo "<p style='color: green;'>✓ API call successful</p>";
                    
                    $data = json_decode($result, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        echo "<p style='color: green;'>✓ Valid JSON response</p>";
                        
                        if (isset($data['error'])) {
                            echo "<p style='color: red;'>✗ API returned error: " . $data['error'] . "</p>";
                        } else {
                            echo "<p style='color: green;'>✓ No errors in response</p>";
                        }
                        
                        echo "<h4>Response Data:</h4>";
                        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                        
                    } else {
                        echo "<p style='color: red;'>✗ Invalid JSON response</p>";
                        echo "<h4>Raw Response:</h4>";
                        echo "<pre>" . htmlspecialchars($result) . "</pre>";
                    }
                } else {
                    echo "<p style='color: red;'>✗ API call failed</p>";
                    $error = error_get_last();
                    if ($error) {
                        echo "<p>Error: " . $error['message'] . "</p>";
                    }
                }
                
            } else {
                echo "<p style='color: red;'>✗ File does not exist</p>";
            }
            echo "</div>";
        }
    }
    ?>
    
    <div class="test-section">
        <h3>Database Connection Test</h3>
        <?php
        try {
            include_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                echo "<p style='color: green;'>✓ Database connection successful</p>";
                
                $stmt = $db->query("SELECT COUNT(*) as count FROM sensor_data");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Total records: " . $count['count'] . "</p>";
                
                if ($count['count'] > 0) {
                    echo "<p style='color: green;'>✓ Data exists in database</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ No data in database - run sample_data.php</p>";
                }
                
            } else {
                echo "<p style='color: red;'>✗ Database connection failed</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h3>File Permissions Check</h3>
        <?php
        $files = [
            'config/database.php',
            'api/get_latest_data.php',
            'api/get_historical_data.php',
            'api/get_water_temp_data.php'
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                if (is_readable($file)) {
                    echo "<p style='color: green;'>✓ $file is readable</p>";
                } else {
                    echo "<p style='color: red;'>✗ $file is not readable</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ $file does not exist</p>";
            }
        }
        ?>
    </div>
</body>
</html>