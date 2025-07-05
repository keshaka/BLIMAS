<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battery Status Monitor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .battery-card { border: 1px solid #ccc; border-radius: 8px; padding: 20px; margin: 10px 0; }
        .charging { background-color: #e8f5e8; }
        .not-charging { background-color: #fff5f5; }
        .battery-level { font-size: 24px; font-weight: bold; }
        .timestamp { color: #666; font-size: 14px; }
        .form-container { background: #f5f5f5; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Battery Status Monitor</h1>
    
    <!-- Add new battery status -->
    <div class="form-container">
        <h2>Add Battery Status</h2>
        <form method="POST">
            <label>Battery Percentage: 
                <input type="number" name="battery_percentage" min="0" max="100" required>
            </label><br><br>
            <label>
                <input type="checkbox" name="is_charging" value="1"> Charging
            </label><br><br>
            <button type="submit">Add Status</button>
        </form>
    </div>

    <?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "battery_db";

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Handle form submission
        if ($_POST) {
            $battery_percentage = $_POST['battery_percentage'];
            $is_charging = isset($_POST['is_charging']) ? 1 : 0;
            
            $stmt = $pdo->prepare("INSERT INTO battery_status (battery_percentage, is_charging) VALUES (?, ?)");
            $stmt->execute([$battery_percentage, $is_charging]);
            
            echo "<p style='color: green;'>Battery status added successfully!</p>";
        }

        // Fetch and display battery status records
        $stmt = $pdo->query("SELECT * FROM battery_status ORDER BY timestamp DESC LIMIT 20");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Recent Battery Status</h2>";
        
        foreach ($records as $record) {
            $chargingClass = $record['is_charging'] ? 'charging' : 'not-charging';
            $chargingText = $record['is_charging'] ? 'Charging' : 'Not Charging';
            
            echo "<div class='battery-card $chargingClass'>";
            echo "<div class='battery-level'>{$record['battery_percentage']}%</div>";
            echo "<div>Status: $chargingText</div>";
            echo "<div class='timestamp'>Recorded: {$record['timestamp']}</div>";
            echo "</div>";
        }

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    ?>
</body>
</html>