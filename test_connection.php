<?php
// Test database connection and data
include_once 'config/database.php';

echo "<h2>BLIMAS Database Connection Test</h2>\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>\n";
        
        // Test if table exists
        $tableCheck = $db->query("SHOW TABLES LIKE 'sensor_data'");
        if ($tableCheck->rowCount() > 0) {
            echo "<p style='color: green;'>✓ sensor_data table exists!</p>\n";
            
            // Check record count
            $countQuery = $db->query("SELECT COUNT(*) as count FROM sensor_data");
            $count = $countQuery->fetch(PDO::FETCH_ASSOC);
            echo "<p>Total records in database: <strong>" . $count['count'] . "</strong></p>\n";
            
            if ($count['count'] > 0) {
                // Show latest record
                $latestQuery = $db->query("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1");
                $latest = $latestQuery->fetch(PDO::FETCH_ASSOC);
                
                echo "<h3>Latest Record:</h3>\n";
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
                echo "<tr><th>Field</th><th>Value</th></tr>\n";
                foreach ($latest as $key => $value) {
                    echo "<tr><td>$key</td><td>$value</td></tr>\n";
                }
                echo "</table>\n";
                
                // Test API endpoints
                echo "<h3>API Test:</h3>\n";
                echo "<p><a href='api/get_latest_data.php' target='_blank'>Test Latest Data API</a></p>\n";
                echo "<p><a href='api/get_historical_data.php?type=air_temperature&period=day' target='_blank'>Test Historical Data API</a></p>\n";
                echo "<p><a href='api/get_water_temp_data.php?period=day' target='_blank'>Test Water Temperature API</a></p>\n";
                
            } else {
                echo "<p style='color: orange;'>⚠ No data found. Run sample_data.php to insert test data.</p>\n";
            }
            
        } else {
            echo "<p style='color: red;'>✗ sensor_data table does not exist!</p>\n";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>\n";
}
?>