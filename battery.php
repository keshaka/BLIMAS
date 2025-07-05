<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battery Status Monitor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: white;
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }

        .form-container h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .form-row {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        label {
            font-weight: 600;
            color: #555;
        }

        input[type="number"] {
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            width: 120px;
        }

        input[type="number"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }

        .records-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }

        .records-section h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8rem;
            text-align: center;
        }

        .records-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .battery-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }

        .battery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .battery-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .charging::before {
            background: linear-gradient(90deg, #28a745, #20c997);
        }

        .not-charging::before {
            background: linear-gradient(90deg, #dc3545, #fd7e14);
        }

        .battery-level {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .charging .battery-level {
            color: #28a745;
        }

        .not-charging .battery-level {
            color: #dc3545;
        }

        .status-text {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .charging .status-text {
            color: #28a745;
        }

        .not-charging .status-text {
            color: #dc3545;
        }

        .timestamp {
            color: #666;
            font-size: 0.9rem;
            font-style: italic;
        }

        .battery-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .no-records {
            text-align: center;
            color: #666;
            font-style: italic;
            font-size: 1.1rem;
            padding: 40px;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                align-items: stretch;
            }

            input[type="number"] {
                width: 100%;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔋 Battery Status Monitor</h1>
        
        <!-- Add new battery status -->
        <div class="form-container">
            <h2>Add New Battery Status</h2>
            <form method="POST">
                <div class="form-row">
                    <label>Battery Percentage:</label>
                    <input type="number" name="battery_percentage" min="0" max="100" placeholder="0-100" required>
                    
                    <div class="checkbox-container">
                        <input type="checkbox" name="is_charging" value="1" id="charging">
                        <label for="charging">Currently Charging</label>
                    </div>
                    
                    <button type="submit">Add Status</button>
                </div>
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
                
                echo "<div class='success-message'>✅ Battery status added successfully!</div>";
            }

            // Fetch and display battery status records
            $stmt = $pdo->query("SELECT * FROM battery_status ORDER BY timestamp DESC LIMIT 20");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<div class='records-section'>";
            echo "<h2>📊 Recent Battery Status</h2>";
            
            if (count($records) > 0) {
                echo "<div class='records-grid'>";
                
                foreach ($records as $record) {
                    $chargingClass = $record['is_charging'] ? 'charging' : 'not-charging';
                    $chargingText = $record['is_charging'] ? '⚡ Charging' : '🔌 Not Charging';
                    $batteryIcon = $record['battery_percentage'] > 75 ? '🔋' : 
                                  ($record['battery_percentage'] > 50 ? '🔋' : 
                                  ($record['battery_percentage'] > 25 ? '🪫' : '🪫'));
                    
                    echo "<div class='battery-card $chargingClass'>";
                    echo "<div class='battery-level'>$batteryIcon {$record['battery_percentage']}%</div>";
                    echo "<div class='status-text'>$chargingText</div>";
                    echo "<div class='timestamp'>📅 Recorded: {$record['timestamp']}</div>";
                    echo "</div>";
                }
                
                echo "</div>";
            } else {
                echo "<div class='no-records'>No battery status records found. Add your first record above!</div>";
            }
            
            echo "</div>";

        } catch(PDOException $e) {
            echo "<div style='color: red; background: #ffe6e6; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "❌ Database Error: " . $e->getMessage();
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>