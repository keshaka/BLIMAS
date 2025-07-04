<?php
/**
 * Water Temperature Analysis Page
 * BLIMAS - Bolgoda Lake Information Monitoring & Analysis System
 */

require_once '../config/database.php';
require_once '../config/config.php';

// Fetch water temperature data from the database
$database = new Database();
$pdo = $database->connect();

$temp1_data = [];
$temp2_data = [];
$temp3_data = [];
$timestamps = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT water_temp1, water_temp2, water_temp3, timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 50");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach (array_reverse($results) as $row) {
            $temp1_data[] = (float)$row['water_temp1'];
            $temp2_data[] = (float)$row['water_temp2'];
            $temp3_data[] = (float)$row['water_temp3'];
            $timestamps[] = $row['timestamp'];
        }
    } catch (Exception $e) {
        error_log("Error fetching water temperature data: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?> - Water Temperature</title>

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

        .depth-visualization {
            display: flex;
            justify-content: center;
            align-items: stretch;
            height: 300px;
            margin: 20px 0;
            background: linear-gradient(to bottom, #87CEEB 0%, #4682B4 50%, #191970 100%);
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .depth-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            color: white;
            text-align: center;
        }

        .depth-level {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .depth-level:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .depth-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .depth-temp {
            font-size: 2rem;
            font-weight: 700;
            margin: 5px 0;
        }

        .depth-info {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .temperature-gradient {
            position: absolute;
            right: 20px;
            top: 20px;
            bottom: 20px;
            width: 30px;
            background: linear-gradient(to bottom, #ff4757 0%, #ffa502 25%, #2ed573 50%, #3742fa 75%, #5352ed 100%);
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .gradient-labels {
            position: absolute;
            right: 60px;
            top: 20px;
            bottom: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
            font-size: 0.8rem;
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
                <span>Water Temperature</span>
            </div>
        </div>
        
        <div class="container">
            <h1 style="text-align: center; margin: 20px 0;">Multi-Depth Water Temperature Analysis</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">
                Monitoring water temperature at three different depths in Bolgoda Lake using DS18B20 sensors
            </p>
        </div>

        <!-- Depth Visualization -->
        <div class="container">
            <div class="chart-container">
                <h3 style="text-align: center; margin-bottom: 20px;">Current Water Temperature by Depth</h3>
                <div class="depth-visualization">
                    <div class="depth-container">
                        <div class="depth-level" style="background: rgba(255, 107, 107, 0.3);">
                            <div class="depth-title">Surface Level (Depth 1)</div>
                            <div class="depth-temp"><?php echo !empty($temp1_data) ? number_format(end($temp1_data), 1) : '--'; ?>°C</div>
                            <div class="depth-info">0-1m depth • Most affected by weather</div>
                        </div>
                        
                        <div class="depth-level" style="background: rgba(52, 152, 219, 0.3);">
                            <div class="depth-title">Middle Level (Depth 2)</div>
                            <div class="depth-temp"><?php echo !empty($temp2_data) ? number_format(end($temp2_data), 1) : '--'; ?>°C</div>
                            <div class="depth-info">1-3m depth • Transition zone</div>
                        </div>
                        
                        <div class="depth-level" style="background: rgba(25, 25, 112, 0.5);">
                            <div class="depth-title">Bottom Level (Depth 3)</div>
                            <div class="depth-temp"><?php echo !empty($temp3_data) ? number_format(end($temp3_data), 1) : '--'; ?>°C</div>
                            <div class="depth-info">3m+ depth • Most stable temperature</div>
                        </div>
                    </div>
                    
                    <div class="temperature-gradient"></div>
                    <div class="gradient-labels">
                        <span>Hot</span>
                        <span>Warm</span>
                        <span>Moderate</span>
                        <span>Cool</span>
                        <span>Cold</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="chart-container">
            <canvas id="waterTempChart"></canvas>
        </div>

        <!-- Current Statistics -->
        <div class="container" style="margin-top: 30px;">
            <div class="dashboard-grid">
                <div class="dashboard-card" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Surface Temperature</h4>
                        <i class="fa fa-thermometer-quarter card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($temp1_data) ? number_format(end($temp1_data), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                    <div class="card-status">Most variable</div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #0984e3 0%, #2d3436 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Middle Temperature</h4>
                        <i class="fa fa-thermometer-half card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($temp2_data) ? number_format(end($temp2_data), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                    <div class="card-status">Transition zone</div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Bottom Temperature</h4>
                        <i class="fa fa-thermometer-three-quarters card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($temp3_data) ? number_format(end($temp3_data), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                    <div class="card-status">Most stable</div>
                </div>
            </div>
        </div>

        <!-- Temperature Analysis -->
        <div class="container" style="margin-top: 30px;">
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="dashboard-card" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Average Surface</h4>
                        <i class="fa fa-calculator card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($temp1_data) ? number_format(array_sum($temp1_data) / count($temp1_data), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Average Middle</h4>
                        <i class="fa fa-calculator card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($temp2_data) ? number_format(array_sum($temp2_data) / count($temp2_data), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Average Bottom</h4>
                        <i class="fa fa-calculator card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($temp3_data) ? number_format(array_sum($temp3_data) / count($temp3_data), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Temperature Gradient</h4>
                        <i class="fa fa-line-chart card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php 
                        if (!empty($temp1_data) && !empty($temp3_data)) {
                            echo number_format(abs(end($temp1_data) - end($temp3_data)), 1);
                        } else {
                            echo '--';
                        }
                        ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                    <div class="card-status">Surface vs Bottom</div>
                </div>
            </div>
        </div>

        <!-- Water Temperature Information -->
        <div class="container" style="margin-top: 30px;">
            <div class="chart-container">
                <h3 style="text-align: center; margin-bottom: 20px;">Water Temperature Information</h3>
                <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div class="dashboard-card" style="background: linear-gradient(135deg, #00cec9 0%, #00b894 100%);">
                        <h4>Surface Water</h4>
                        <p>Most affected by weather conditions, daily temperature fluctuations, and solar heating. Typically shows the highest variation.</p>
                    </div>
                    <div class="dashboard-card" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                        <h4>Middle Water</h4>
                        <p>Transition zone between surface and deep water. Shows moderate temperature variation and acts as a buffer zone.</p>
                    </div>
                    <div class="dashboard-card" style="background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);">
                        <h4>Deep Water</h4>
                        <p>Most stable temperature zone. Less affected by surface conditions and maintains more consistent temperature year-round.</p>
                    </div>
                    <div class="dashboard-card" style="background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);">
                        <h4>Thermal Stratification</h4>
                        <p>Natural layering of water by temperature. Important for lake ecosystem health and water quality management.</p>
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
var temp1Data = <?php echo json_encode($temp1_data); ?>;
var temp2Data = <?php echo json_encode($temp2_data); ?>;
var temp3Data = <?php echo json_encode($temp3_data); ?>;
var timestamps = <?php echo json_encode($timestamps); ?>;

// Format timestamps for readability
var formattedTimestamps = timestamps.map(function(timestamp) {
    var date = new Date(timestamp);
    return date.toLocaleTimeString();
});

// Get the canvas context
var ctx = document.getElementById('waterTempChart').getContext('2d');

// Create the chart
var waterTempChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: formattedTimestamps,
        datasets: [{
            label: 'Surface Temperature (°C)',
            data: temp1Data,
            borderColor: '#74b9ff',
            backgroundColor: 'rgba(116, 185, 255, 0.1)',
            borderWidth: 3,
            fill: false,
            tension: 0.4,
            pointBackgroundColor: '#74b9ff',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 8
        }, {
            label: 'Middle Temperature (°C)',
            data: temp2Data,
            borderColor: '#0984e3',
            backgroundColor: 'rgba(9, 132, 227, 0.1)',
            borderWidth: 3,
            fill: false,
            tension: 0.4,
            pointBackgroundColor: '#0984e3',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 8
        }, {
            label: 'Bottom Temperature (°C)',
            data: temp3Data,
            borderColor: '#2d3436',
            backgroundColor: 'rgba(45, 52, 54, 0.1)',
            borderWidth: 3,
            fill: false,
            tension: 0.4,
            pointBackgroundColor: '#2d3436',
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
                    text: 'Temperature (°C)'
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
                borderColor: '#74b9ff',
                borderWidth: 1
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