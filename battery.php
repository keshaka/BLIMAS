<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battery Status Monitor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }

        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .mobile-menu {
            display: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .main-content {
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

        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }

        .chart-container h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8rem;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
            margin-bottom: 20px;
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

        .no-records {
            text-align: center;
            color: #666;
            font-style: italic;
            font-size: 1.1rem;
            padding: 40px;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu {
                display: block;
            }

            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .back-button {
                order: -1;
            }

            h1 {
                font-size: 2rem;
            }
            
            .chart-wrapper {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">BLIMAS</a>
            <a href="index.php" class="back-button">
                ← Back to Dashboard
            </a>
            <div class="mobile-menu">☰</div>
        </nav>
    </header>
    <div class="main-content">
</head>
<body>
    <div class="container">
        <h1>🔋 Battery Status Monitor</h1>
        
        <?php
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "blimas_db";

        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Fetch battery status records for chart
            $stmt = $pdo->query("SELECT * FROM battery_status ORDER BY timestamp ASC LIMIT 50");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($records) > 0) {
                // Prepare data for chart
                $timestamps = [];
                $batteryLevels = [];
                $chargingStatus = [];
                
                foreach ($records as $record) {
                    $timestamps[] = date('M j, H:i', strtotime($record['timestamp']));
                    $batteryLevels[] = $record['battery_percentage'];
                    $chargingStatus[] = $record['is_charging'];
                }
                
                echo "<div class='chart-container'>";
                echo "<h2>📈 Battery Level Trend</h2>";
                echo "<div class='chart-wrapper'>";
                echo "<canvas id='batteryChart'></canvas>";
                echo "</div>";
                echo "</div>";
            }

            // Fetch recent records for display
            $stmt = $pdo->query("SELECT * FROM battery_status ORDER BY timestamp DESC LIMIT 1");
            $recentRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<div class='records-section'>";
            echo "<h2>📊 Recent Battery Status</h2>";
            
            if (count($recentRecords) > 0) {
                echo "<div class='records-grid'>";
                
                foreach ($recentRecords as $record) {
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
                echo "<div class='no-records'>No battery status records found.</div>";
            }
            
            echo "</div>";

        } catch(PDOException $e) {
            echo "<div style='color: red; background: #ffe6e6; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "❌ Database Error: " . $e->getMessage();
            echo "</div>";
        }
        ?>
    </div>

    <?php if (isset($timestamps) && count($timestamps) > 0): ?>
    <script>
        const ctx = document.getElementById('batteryChart').getContext('2d');
        const timestamps = <?php echo json_encode($timestamps); ?>;
        const batteryLevels = <?php echo json_encode($batteryLevels); ?>;
        const chargingStatus = <?php echo json_encode($chargingStatus); ?>;
        
        // Create background colors based on charging status
        const backgroundColors = chargingStatus.map(isCharging => 
            isCharging ? 'rgba(40, 167, 69, 0.2)' : 'rgba(220, 53, 69, 0.2)'
        );
        
        const borderColors = chargingStatus.map(isCharging => 
            isCharging ? 'rgba(40, 167, 69, 1)' : 'rgba(220, 53, 69, 1)'
        );

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timestamps,
                datasets: [{
                    label: 'Battery Level (%)',
                    data: batteryLevels,
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: borderColors,
                    pointBorderColor: borderColors,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                return chargingStatus[index] ? '⚡ Charging' : '🔌 Not Charging';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            maxTicksLimit: 8,
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>