<?php
require_once 'includes/database.php';

// Get water level data from the database
try {
    $conn = DatabaseConfig::getMySQLiConnection();
    $sql = "SELECT water_level, timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 50";
    $result = $conn->query($sql);
    
    // Arrays to store the data for the graph
    $waterLevels = [];
    $timestamps = [];
    
    // Fetch the data into the arrays
    while ($row = $result->fetch_assoc()) {
        $waterLevels[] = $row['water_level'];
        $timestamps[] = $row['timestamp'];
    }
    
    $conn->close();
} catch (Exception $e) {
    // Handle error gracefully
    $waterLevels = [];
    $timestamps = [];
    error_log("Water level page database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>BLIMAS</title>

    <!-- Loading third party fonts -->
    <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
    <link href="fonts/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Loading main css file -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/enhanced.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Body setup */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden; /* Prevent overall page scroll */
            flex-direction: column;
        }

        /* Centered title */
        h1 {
            text-align: center;  /* Center-align the text horizontally */
            font-size: 2rem;
            margin-bottom: 20px;
        }

        /* Container for the canvas */
        .chart-container {
            width: 100%;
            max-width: 900px; /* Limit maximum width */
            height: 400px;  /* Set a fixed height */
            overflow: hidden; /* Prevent canvas from growing beyond container */
        }

        /* The canvas itself */
        canvas {
            width: 100%;
            height: 100% !important; /* Make canvas fill container */
            display: block; /* Prevent default inline behavior */
        }

    </style>
</head>
<body>

<div class="site-content">
    <div class="site-header">
        <div class="container">
            <a href="index.html" class="branding">
                <img src="images/logo.png" alt="" class="logo">
                <div class="logo-type">
                    <h1 class="site-title">BLIMAS</h1>
                    <small class="site-description">Bolgoda Lake Information & Analysis system</small>
                </div>
            </a>

            <!-- Default snippet for navigation -->
            <div class="main-navigation">
                <button type="button" class="menu-toggle"><i class="fa fa-bars"></i></button>
                <ul class="menu">
                    <li class="menu-item"><a href="index.html">Home</a></li>
                    <li class="menu-item current-menu-item"><a href="water-level.php">Water Level</a></li>
                    <li class="menu-item"><a href="temp.php">Temperature</a></li>
					<li class="menu-item"><a href="humidity.php">Humidity</a></li>
					<li class="menu-item"><a href="watertmp.php">Water Temparature</a></li>
                </ul> <!-- .menu -->
            </div> <!-- .main-navigation -->

            <div class="mobile-navigation"></div>
        </div>
    </div> <!-- .site-header -->

    <main class="main-content">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.html">Home</a>
                <span>Water Level</span>
            </div>
        </div>
        <div class="container">
            <h1>Water Level Over Time</h1>
        </div>
    </main> <!-- .main-content -->

    <!-- Container for the canvas, now we limit the height and prevent stretching -->
    <div class="chart-container">
        <canvas id="waterLevelChart"></canvas>
    </div>

    <script>
        // Get PHP data and pass to JavaScript
        var waterLevels = <?php echo json_encode($waterLevels); ?>;
        var timestamps = <?php echo json_encode($timestamps); ?>;

        // Format timestamps for readability
        var formattedTimestamps = timestamps.map(function(timestamp) {
            var date = new Date(timestamp);
            return date.toLocaleString();  // Converts timestamp to a readable date/time string
        });

        // Get the canvas context
        var ctx = document.getElementById('waterLevelChart').getContext('2d');

        // Create the chart
        var waterLevelChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: formattedTimestamps,
                datasets: [{
                    label: 'Water Level (cm)',
                    data: waterLevels,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,  // Allow the chart to use full container size
                scales: {
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxRotation: 90,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    </script>

    <footer class="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <form action="#" class="subscribe-form">
                        <input type="text" placeholder="Enter your email to subscribe...">
                        <input type="submit" value="Subscribe">
                    </form>
                </div>
                <div class="col-md-3 col-md-offset-1">
                    <div class="social-links">
                        <a href="#"><i class="fa fa-facebook"></i></a>
                        <a href="#"><i class="fa fa-twitter"></i></a>
                        <a href="#"><i class="fa fa-google-plus"></i></a>
                        <a href="#"><i class="fa fa-pinterest"></i></a>
                    </div>
                </div>
            </div>

            <p class="colophon">Copyright 2014 Circuit Sages. Designed by Circuit Sages. All rights reserved</p>
        </div>
    </footer> <!-- .site-footer -->
</div>

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/app.js"></script>

</body>
</html>