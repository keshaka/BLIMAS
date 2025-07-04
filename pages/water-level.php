<?php
/**
 * Water Level Monitoring Page
 * BLIMAS - Bolgoda Lake Information Monitoring & Analysis System
 */

require_once '../config/database.php';
require_once '../config/config.php';

// Fetch water level data from the database
$database = new Database();
$pdo = $database->connect();

$water_levels = [];
$timestamps = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT water_level, timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 50");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach (array_reverse($results) as $row) {
            $water_levels[] = (float)$row['water_level'];
            $timestamps[] = $row['timestamp'];
        }
    } catch (Exception $e) {
        error_log("Error fetching water level data: " . $e->getMessage());
    }
}

// Calculate water level status
function getWaterLevelStatus($level) {
    if ($level > 200) return ['status' => 'high', 'color' => '#e74c3c', 'text' => 'High Level'];
    if ($level > 150) return ['status' => 'normal-high', 'color' => '#f39c12', 'text' => 'Above Normal'];
    if ($level > 100) return ['status' => 'normal', 'color' => '#27ae60', 'text' => 'Normal'];
    if ($level > 50) return ['status' => 'normal-low', 'color' => '#f39c12', 'text' => 'Below Normal'];
    return ['status' => 'low', 'color' => '#e74c3c', 'text' => 'Low Level'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?> - Water Level</title>

    <!-- Loading third party fonts -->
    <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
    <link href="../fonts/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Loading main css file -->
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 100%;
            max-width: 1200px;
            height: 500px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .water-level-visual {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            height: 200px;
            margin: 20px 0;
            background: linear-gradient(to bottom, #87CEEB 0%, #4682B4 100%);
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .water-indicator {
            width: 100px;
            background: linear-gradient(to top, #0077be 0%, #00a8ff 50%, #74b9ff 100%);
            border-radius: 10px 10px 0 0;
            position: relative;
            transition: height 0.5s ease;
        }

        .water-indicator::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: wave 2s ease-in-out infinite;
        }

        @keyframes wave {
            0%, 100% { transform: translateY(0px) scaleX(1); }
            50% { transform: translateY(-5px) scaleX(1.1); }
        }

        .level-markers {
            position: absolute;
            right: 10px;
            top: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 10px 0;
        }

        .marker {
            background: rgba(255, 255, 255, 0.8);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="site-content">
    <div class="site-header">
        <div class="container">
            <a href="../index.php" class="branding">
                <img src="../images/logo.png" alt="" class="logo">
                <div class="logo-type">
                    <h1 class="site-title"><?php echo SITE_NAME; ?></h1>
                    <small class="site-description"><?php echo SITE_DESCRIPTION; ?></small>
                </div>
            </a>

            <!-- Navigation -->
            <?php include '../includes/navigation.php'; ?>

            <div class="mobile-navigation"></div>
        </div>
    </div> <!-- .site-header -->

    <main class="main-content">
        <div class="container">
            <div class="breadcrumb">
                <a href="../index.php">Home</a>
                <span>Water Level</span>
            </div>
        </div>
        
        <div class="container">
            <h1 style="text-align: center; margin: 20px 0;">Water Level Monitoring</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">
                Real-time water level monitoring in Bolgoda Lake using ultrasonic sensor
            </p>
        </div>

        <!-- Visual Water Level Indicator -->
        <div class="container">
            <div class="chart-container">
                <h3 style="text-align: center; margin-bottom: 20px;">Current Water Level</h3>
                <div class="water-level-visual">
                    <div class="water-indicator" id="water-indicator" style="height: <?php echo !empty($water_levels) ? min(80, max(10, (end($water_levels) / 250) * 100)) : 50; ?>%;"></div>
                    <div class="level-markers">
                        <div class="marker">High (200cm+)</div>
                        <div class="marker">Normal (100-200cm)</div>
                        <div class="marker">Low (50-100cm)</div>
                        <div class="marker">Critical (<50cm)</div>
                    </div>
                </div>
                <p style="text-align: center; font-size: 1.2rem; margin-top: 15px;">
                    Current Level: <strong><?php echo !empty($water_levels) ? number_format(end($water_levels), 1) : '--'; ?> cm</strong>
                    <?php if (!empty($water_levels)): 
                        $status = getWaterLevelStatus(end($water_levels));
                    ?>
                    - <span style="color: <?php echo $status['color']; ?>; font-weight: bold;"><?php echo $status['text']; ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="chart-container">
            <canvas id="waterLevelChart"></canvas>
        </div>

        <!-- Statistics -->
        <div class="container" style="margin-top: 30px;">
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="dashboard-card card-water-level">
                    <div class="card-header">
                        <h4 class="card-title">Current Level</h4>
                        <i class="fa fa-water card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span id="current-level"><?php echo !empty($water_levels) ? number_format(end($water_levels), 1) : '--'; ?></span>
                        <span class="card-unit">cm</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Average (24h)</h4>
                        <i class="fa fa-calculator card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($water_levels) ? number_format(array_sum($water_levels) / count($water_levels), 1) : '--'; ?></span>
                        <span class="card-unit">cm</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #0984e3 0%, #74b9ff 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Maximum</h4>
                        <i class="fa fa-arrow-up card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($water_levels) ? number_format(max($water_levels), 1) : '--'; ?></span>
                        <span class="card-unit">cm</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Minimum</h4>
                        <i class="fa fa-arrow-down card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($water_levels) ? number_format(min($water_levels), 1) : '--'; ?></span>
                        <span class="card-unit">cm</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Water Level Information -->
        <div class="container" style="margin-top: 30px;">
            <div class="chart-container">
                <h3 style="text-align: center; margin-bottom: 20px;">Water Level Status Guide</h3>
                <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div class="dashboard-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                        <h4>Normal Range</h4>
                        <p><strong>100-200cm</strong> - Ideal water level for lake ecosystem</p>
                    </div>
                    <div class="dashboard-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                        <h4>High Level</h4>
                        <p><strong>200cm+</strong> - May indicate heavy rainfall or flooding</p>
                    </div>
                    <div class="dashboard-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <h4>Low Level</h4>
                        <p><strong>50-100cm</strong> - Below normal, monitor closely</p>
                    </div>
                    <div class="dashboard-card" style="background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);">
                        <h4>Critical Level</h4>
                        <p><strong><50cm</strong> - Requires immediate attention</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p class="colophon">Copyright <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved</p>
        </div>
    </footer>
</div>

<script src="../js/jquery-1.11.1.min.js"></script>
<script src="../js/plugins.js"></script>
<script src="../js/app.js"></script>

<script>
// Get PHP data and pass to JavaScript
var waterLevels = <?php echo json_encode($water_levels); ?>;
var timestamps = <?php echo json_encode($timestamps); ?>;

// Format timestamps for readability
var formattedTimestamps = timestamps.map(function(timestamp) {
    var date = new Date(timestamp);
    return date.toLocaleTimeString();
});

// Get the canvas context
var ctx = document.getElementById('waterLevelChart').getContext('2d');

// Create the chart with threshold lines
var waterLevelChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: formattedTimestamps,
        datasets: [{
            label: 'Water Level (cm)',
            data: waterLevels,
            borderColor: '#45b7d1',
            backgroundColor: 'rgba(69, 183, 209, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#45b7d1',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Time'
                },
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Water Level (cm)'
                },
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleColor: 'white',
                bodyColor: 'white',
                borderColor: '#45b7d1',
                borderWidth: 1,
                callbacks: {
                    afterLabel: function(context) {
                        var level = context.parsed.y;
                        if (level > 200) return 'Status: High Level';
                        if (level > 100) return 'Status: Normal';
                        if (level > 50) return 'Status: Below Normal';
                        return 'Status: Low Level';
                    }
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        },
        animation: {
            duration: 1000,
            easing: 'easeInOutQuart'
        }
    }
});

// Auto-refresh data every 30 seconds
setInterval(function() {
    // You can implement AJAX refresh here
    console.log('Auto-refresh: ' + new Date().toLocaleTimeString());
}, 30000);
</script>

</body>
</html>