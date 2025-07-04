<?php
/**
 * Temperature Analysis Page
 * BLIMAS - Bolgoda Lake Information Monitoring & Analysis System
 */

require_once '../config/database.php';
require_once '../config/config.php';

// Fetch temperature data from the database
$database = new Database();
$pdo = $database->connect();

$air_temps = [];
$timestamps = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT air_temp, timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 50");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach (array_reverse($results) as $row) {
            $air_temps[] = (float)$row['air_temp'];
            $timestamps[] = $row['timestamp'];
        }
    } catch (Exception $e) {
        error_log("Error fetching temperature data: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?> - Air Temperature</title>

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
                <span>Air Temperature</span>
            </div>
        </div>
        
        <div class="container">
            <h1 style="text-align: center; margin: 20px 0;">Air Temperature Analysis</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">
                Real-time air temperature monitoring from DHT11 sensor
            </p>
        </div>

        <!-- Chart Container -->
        <div class="chart-container">
            <canvas id="temperatureChart"></canvas>
        </div>

        <!-- Statistics -->
        <div class="container" style="margin-top: 30px;">
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="dashboard-card card-temperature">
                    <div class="card-header">
                        <h4 class="card-title">Current Temperature</h4>
                        <i class="fa fa-thermometer-half card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span id="current-temp"><?php echo !empty($air_temps) ? number_format(end($air_temps), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Average (24h)</h4>
                        <i class="fa fa-calculator card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($air_temps) ? number_format(array_sum($air_temps) / count($air_temps), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #0984e3 0%, #74b9ff 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Maximum</h4>
                        <i class="fa fa-arrow-up card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($air_temps) ? number_format(max($air_temps), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Minimum</h4>
                        <i class="fa fa-arrow-down card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span><?php echo !empty($air_temps) ? number_format(min($air_temps), 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
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
var airTemps = <?php echo json_encode($air_temps); ?>;
var timestamps = <?php echo json_encode($timestamps); ?>;

// Format timestamps for readability
var formattedTimestamps = timestamps.map(function(timestamp) {
    var date = new Date(timestamp);
    return date.toLocaleTimeString();
});

// Get the canvas context
var ctx = document.getElementById('temperatureChart').getContext('2d');

// Create the chart
var temperatureChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: formattedTimestamps,
        datasets: [{
            label: 'Air Temperature (°C)',
            data: airTemps,
            borderColor: '#ff6b6b',
            backgroundColor: 'rgba(255, 107, 107, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ff6b6b',
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
                borderColor: '#ff6b6b',
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