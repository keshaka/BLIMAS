<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Temperature - BLIMAS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">BLIMAS</a>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="temperature.php">Temperature</a></li>
                <li><a href="humidity.php">Humidity</a></li>
                <li><a href="water-level.php">Water Level</a></li>
                <li><a href="water-temperature.php" class="active">Water Temperature</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content page-enter">
        <h1 class="page-title">Water Temperature Monitoring</h1>
        
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Water Temperature by Depth</h3>
                <div class="time-selector">
                    <button class="time-btn active" onclick="loadWaterTempChart(this, 6)">6H</button>
                    <button class="time-btn" onclick="loadWaterTempChart(this, 24)">24H</button>
                    <button class="time-btn" onclick="loadWaterTempChart(this, 72)">3D</button>
                    <button class="time-btn" onclick="loadWaterTempChart(this, 168)">7D</button>
                </div>
            </div>
            <div style="height: 400px;">
                <canvas id="water-temp-chart"></canvas>
            </div>
        </div>

        <div class="water-temp-grid">
            <div class="depth-card">
                <div class="depth-label">Surface (Depth 1)</div>
                <div class="depth-value" id="current-temp-1">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
            <div class="depth-card">
                <div class="depth-label">Middle (Depth 2)</div>
                <div class="depth-value" id="current-temp-2">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
            <div class="depth-card">
                <div class="depth-label">Bottom (Depth 3)</div>
                <div class="depth-value" id="current-temp-3">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-adapter-date-fns/2.0.0/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        async function loadWaterTempChart(button, hours) {
            // Update active button
            document.querySelectorAll('.time-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Show loading
            const chartContainer = document.getElementById('water-temp-chart').parentElement;
            chartContainer.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
            
            try {
                const data = await window.chartManager.loadHistoricalData('water_temperature', hours);
                
                // Restore canvas
                chartContainer.innerHTML = '<canvas id="water-temp-chart"></canvas>';
                
                if (data.length > 0) {
                    window.chartManager.createWaterTemperatureChart('water-temp-chart', data);
                    updateWaterTempDisplay(data);
                } else {
                    chartContainer.innerHTML = '<div class="text-center">No data available for the selected time period</div>';
                }
            } catch (error) {
                console.error('Error loading water temperature chart:', error);
                chartContainer.innerHTML = '<div class="text-center">Error loading chart data</div>';
            }
        }

        function updateWaterTempDisplay(data) {
            if (data.length === 0) return;
            
            const latest = data[data.length - 1];
            document.getElementById('current-temp-1').textContent = parseFloat(latest.water_temp_depth1).toFixed(1);
            document.getElementById('current-temp-2').textContent = parseFloat(latest.water_temp_depth2).toFixed(1);
            document.getElementById('current-temp-3').textContent = parseFloat(latest.water_temp_depth3).toFixed(1);
        }

        // Load initial chart when page loads
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const activeButton = document.querySelector('.time-btn.active');
                loadWaterTempChart(activeButton, 6);
            }, 500);
        });
    </script>
</body>
</html>