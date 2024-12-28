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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distance Data Graph</title>
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Some basic styles for the graph container */
        canvas {
            width: 100%;
            height: 400px;
        }
    </style>
</head>
<body>

<h1>Distance Data (Latest 50 Entries)</h1>

<!-- Canvas element for Chart.js to draw the graph -->
<canvas id="distanceChart"></canvas>

<script>
    // Get the PHP data and encode it into JavaScript arrays
    var distances = <?php echo json_encode($distances); ?>;
    var timestamps = <?php echo json_encode($timestamps); ?>;

    // Format timestamps (optional, if needed, to display in a more readable format)
    var formattedTimestamps = timestamps.map(function(timestamp) {
        var date = new Date(timestamp);
        return date.toLocaleString();  // Converts timestamp to a readable date/time string
    });

    // Create the chart
    var ctx = document.getElementById('distanceChart').getContext('2d');
    var distanceChart = new Chart(ctx, {
        type: 'line',  // Line chart
        data: {
            labels: formattedTimestamps,  // X-axis labels (timestamps)
            datasets: [{
                label: 'Distance',
                data: distances,  // Y-axis data (distance values)
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,  // Fill under the line
                tension: 0.4  // Smooth the line
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: {
                        autoSkip: true,
                        maxRotation: 90,  // Rotate labels if they overlap
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: false  // Adjust based on your data, if necessary
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return 'Distance: ' + tooltipItem.raw + ' units';
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>