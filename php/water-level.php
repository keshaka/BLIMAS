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

// Fetch the distance data from the database
$sql = "SELECT distance, timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 50"; // Limit to the latest 50 records
$result = $conn->query($sql);

// Arrays to store the data for the graph
$distances = [];
$timestamps = [];

// Fetch the data into the arrays
while ($row = $result->fetch_assoc()) {
    $distances[] = $row['distance'];
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
    <title>Distance Data Graph</title>
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
            background-color: #f4f4f4;
            overflow: hidden; /* Prevent overall page scroll */
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
            height: 100%; /* Stretch the canvas to fill container */
        }

        h1 {
            text-align: center;
            font-size: 2rem;
        }
    </style>
</head>
<body>

<h1>Distance Data (Latest 50 Entries)</h1>

<!-- Container for the canvas, now we limit the height and prevent stretching -->
<div class="chart-container">
    <canvas id="distanceChart"></canvas>
</div>

<script>
    // Get PHP data and pass to JavaScript
    var distances = <?php echo json_encode($distances); ?>;
    var timestamps = <?php echo json_encode($timestamps); ?>;

    // Format timestamps for readability
    var formattedTimestamps = timestamps.map(function(timestamp) {
        var date = new Date(timestamp);
        return date.toLocaleString();  // Converts timestamp to a readable date/time string
    });

    var ctx = document.getElementById('distanceChart').getContext('2d');
    var distanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: formattedTimestamps,
            datasets: [{
                label: 'Distance',
                data: distances,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,  // Maintain aspect ratio to avoid stretching
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

</body>
</html>