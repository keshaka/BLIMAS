<?php
// Database connection parameters
$servername = "sensor.cxkk2guqqpwa.ap-southeast-1.rds.amazonaws.com";
$username = "keshaka";
$password = "alohomora";
$dbname = "sensor_data";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the tempDHT data from the database
$sql = "SELECT tempDHT, timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 50"; // Limit to the latest 50 records
$result = $conn->query($sql);

// Arrays to store the data for the graph
$tempDHT = [];
$timestamps = [];

// Fetch the data into the arrays
while ($row = $result->fetch_assoc()) {
    $tempDHT[] = $row['tempDHT'];
    $timestamps[] = $row['timestamp'];
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>BLIMAS - tempDHT</title>

    <!-- Loading third party fonts -->
    <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
    <link href="fonts/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Loading main css file -->
    <link rel="stylesheet" href="style.css">

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
                    <li class="menu-item"><a href="water-level.php">Water Level</a></li>
                    <li class="menu-item current-menu-item"><a href="temp.php">Temperature</a></li>
                    <li class="menu-item"><a href="humidity.php">Humidity</a></li>
                    <li class="menu-item"><a href="watertmp.php">Underwater Temperature</a></li>
                </ul> <!-- .menu -->
            </div> <!-- .main-navigation -->

            <div class="mobile-navigation"></div>
        </div>
    </div> <!-- .site-header -->

    <main class="main-content">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.html">Home</a>
                <span>Temperature</span>
            </div>
        </div>
        <div class="container">
            <h1>Temperature Over Time</h1>
        </div>
    </main> <!-- .main-content -->

    <!-- Container for the canvas -->
    <div class="chart-container">
        <canvas id="tempDHTChart"></canvas>
    </div>

    <script>
        // Get PHP data and pass to JavaScript
        var tempDHT = <?php echo json_encode($tempDHT); ?>;
        var timestamps = <?php echo json_encode($timestamps); ?>;

        // Format timestamps for readability
        var formattedTimestamps = timestamps.map(function(timestamp) {
            var date = new Date(timestamp);
            return date.toLocaleString();  // Converts timestamp to a readable date/time string
        });

        // Get the canvas context
        var ctx = document.getElementById('tempDHTChart').getContext('2d');

        // Create the chart
        var tempDHTChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: formattedTimestamps,  // Display timestamps from left to right
                datasets: [{
                    label: 'tempDHT (°C)',
                    data: tempDHT,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
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

        // Function to update chart with new data
        function updateChart(newTimestamp, newTempDHT) {
            // Add new data to the right side
            tempDHTChart.data.labels.push(newTimestamp);
            tempDHTChart.data.datasets[0].data.push(newTempDHT);

            // Optionally remove old data to keep the number of data points manageable
            if (tempDHTChart.data.labels.length > 50) {
                tempDHTChart.data.labels.shift(); // Remove the first (oldest) label
                tempDHTChart.data.datasets[0].data.shift(); // Remove the first (oldest) data point
            }

            // Update the chart
            tempDHTChart.update();
        }

        // Example of new data being added (you could call this function periodically)
        // updateChart("2024-12-28 12:30", 25.1);
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