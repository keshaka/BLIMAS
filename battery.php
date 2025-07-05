<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battery Status - BLIMAS</title>
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
                <li><a href="humidity.php">Humidity</a></li>
                <li><a href="water-level.php">Water Level</a></li>
                <li><a href="water-temperature.php">Water Temperature</a></li>
                <li><a href="battery.php" class="active">Battery</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content page-enter">
        <h1 class="page-title">Battery Status Monitoring</h1>
        
        <div class="dashboard-grid">
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">🔋</div>
                    <div class="card-title">Battery Level</div>
                </div>
                <div class="card-value" id="battery-level">--.-</div>
                <div class="card-unit">%</div>
                <div class="status-indicator">
                    <div class="status-dot status-normal" id="battery-status"></div>
                    <span>Normal Range</span>
                </div>
            </div>

            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">⚡</div>
                    <div class="card-title">Voltage</div>
                </div>
                <div class="card-value" id="battery-voltage">--.-</div>
                <div class="card-unit">V</div>
                <div class="status-indicator">
                    <div class="status-dot status-normal" id="voltage-status"></div>
                    <span>Normal Range</span>
                </div>
            </div>

            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">⏰</div>
                    <div class="card-title">Last Update</div>
                </div>
                <div class="card-value" id="last-update" style="font-size: 18px;">--:--</div>
                <div class="card-unit">Timestamp</div>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Battery Level Trends</h3>
                <div class="time-selector">
                    <button class="time-btn active" onclick="loadBatteryChart(this, 6)">6H</button>
                    <button class="time-btn" onclick="loadBatteryChart(this, 24)">24H</button>
                    <button class="time-btn" onclick="loadBatteryChart(this, 72)">3D</button>
                    <button class="time-btn" onclick="loadBatteryChart(this, 168)">7D</button>
                </div>
            </div>
            <div style="height: 400px;">
                <canvas id="battery-chart"></canvas>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">📊</div>
                    <div class="card-title">Current Level</div>
                </div>
                <div class="card-value" id="current-battery">--.-</div>
                <div class="card-unit">%</div>
            </div>

            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">📈</div>
                    <div class="card-title">Maximum (24H)</div>
                </div>
                <div class="card-value" id="max-battery">--.-</div>
                <div class="card-unit">%</div>
            </div>

            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">📉</div>
                    <div class="card-title">Minimum (24H)</div>
                </div>
                <div class="card-value" id="min-battery">--.-</div>
                <div class="card-unit">%</div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        async function loadBatteryChart(button, hours) {
            setActiveButton(button);
            const data = await window.chartManager.loadHistoricalData('battery', hours);
            if (data.length > 0) {
                window.chartManager.createLineChart('battery-chart', data, 'Battery Level (%)', '#4CAF50');
                updateStatsFromData(data, 'battery');
            }
        }

        // Load initial chart when page loads
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const activeButton = document.querySelector('.time-btn.active');
                loadBatteryChart(activeButton, 6);
            }, 500);
        });
    </script>
</body>
</html>