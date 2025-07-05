<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Humidity - BLIMAS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">BLIMAS</a>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="temperature.php">Temperature</a></li>
                <li><a href="humidity.php" class="active">Humidity</a></li>
                <li><a href="water-level.php">Water Level</a></li>
                <li><a href="water-temperature.php">Water Temperature</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content page-enter">
        <h1 class="page-title">Humidity Monitoring</h1>
        
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Humidity Trends</h3>
                <div class="time-selector">
                    <button class="time-btn active" onclick="loadHumidityChart(this, 6)">6H</button>
                    <button class="time-btn" onclick="loadHumidityChart(this, 24)">24H</button>
                    <button class="time-btn" onclick="loadHumidityChart(this, 72)">3D</button>
                    <button class="time-btn" onclick="loadHumidityChart(this, 168)">7D</button>
                </div>
            </div>
            <div style="height: 400px;">
                <canvas id="humidity-chart"></canvas>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">💧</div>
                    <div class="card-title">Current Humidity</div>
                </div>
                <div class="card-value" id="current-humidity">--.-</div>
                <div class="card-unit">%</div>
            </div>
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">📈</div>
                    <div class="card-title">24H Maximum</div>
                </div>
                <div class="card-value" id="max-humidity">--.-</div>
                <div class="card-unit">%</div>
            </div>
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">📉</div>
                    <div class="card-title">24H Minimum</div>
                </div>
                <div class="card-value" id="min-humidity">--.-</div>
                <div class="card-unit">%</div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        async function loadHumidityChart(button, hours) {
            // Update active button
            document.querySelectorAll('.time-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Show loading
            const chartContainer = document.getElementById('humidity-chart').parentElement;
            chartContainer.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
            
            try {
                const data = await window.chartManager.loadHistoricalData('humidity', hours);
                
                // Restore canvas
                chartContainer.innerHTML = '<canvas id="humidity-chart"></canvas>';
                
                if (data.length > 0) {
                    window.chartManager.createLineChart('humidity-chart', data, 'Humidity (%)', '#36A2EB');
                    updateHumidityStats(data);
                } else {
                    chartContainer.innerHTML = '<div class="text-center">No data available for the selected time period</div>';
                }
            } catch (error) {
                console.error('Error loading humidity chart:', error);
                chartContainer.innerHTML = '<div class="text-center">Error loading chart data</div>';
            }
        }

        function updateHumidityStats(data) {
            if (data.length === 0) return;
            
            const values = data.map(item => parseFloat(item.value));
            const current = values[values.length - 1];
            const max = Math.max(...values);
            const min = Math.min(...values);
            
            document.getElementById('current-humidity').textContent = current.toFixed(1);
            document.getElementById('max-humidity').textContent = max.toFixed(1);
            document.getElementById('min-humidity').textContent = min.toFixed(1);
        }

        // Load initial chart when page loads
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const activeButton = document.querySelector('.time-btn.active');
                loadHumidityChart(activeButton, 6);
            }, 500);
        });
    </script>
</body>
</html>