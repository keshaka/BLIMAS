<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLIMAS - Bolgoda Lake Monitoring System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">BLIMAS</a>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="temperature.php">Temperature</a></li>
                <li><a href="humidity.php">Humidity</a></li>
                <li><a href="water-level.php">Water Level</a></li>
                <li><a href="water-temperature.php">Water Temperature</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <h1 class="page-title">Bolgoda Lake Monitoring System</h1>
        
        <div class="dashboard-grid">
            <!-- Air Temperature Card -->
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">🌡️</div>
                    <div class="card-title">Air Temperature</div>
                </div>
                <div class="card-value" id="air-temperature">--.-</div>
                <div class="card-unit">°C</div>
                <div class="status-indicator">
                    <div class="status-dot status-normal" id="temp-status"></div>
                    <span>Normal Range</span>
                </div>
            </div>

            <!-- Humidity Card -->
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">💧</div>
                    <div class="card-title">Humidity</div>
                </div>
                <div class="card-value" id="humidity">--.-</div>
                <div class="card-unit">%</div>
                <div class="status-indicator">
                    <div class="status-dot status-normal" id="humidity-status"></div>
                    <span>Normal Range</span>
                </div>
            </div>

            <!-- Water Level Card -->
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">🌊</div>
                    <div class="card-title">Water Level</div>
                </div>
                <div class="card-value" id="water-level">--.-</div>
                <div class="card-unit">m</div>
                <div class="status-indicator">
                    <div class="status-dot status-normal" id="water-level-status"></div>
                    <span>Normal Range</span>
                </div>
            </div>

            <!-- Weather Widget -->
            <div class="weather-widget">
                <div class="weather-icon" id="weather-icon">🌤️</div>
                <div class="weather-temp" id="weather-temp">--</div>
                <div class="weather-desc" id="weather-desc">Loading...</div>
                <div class="weather-details">
                    <div class="weather-detail">
                        <div>Humidity</div>
                        <div id="weather-humidity">--%</div>
                    </div>
                    <div class="weather-detail">
                        <div>Pressure</div>
                        <div id="weather-pressure">-- hPa</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Water Temperature Section -->
        <div class="water-temp-grid">
            <div class="depth-card">
                <div class="depth-label">Surface (Depth 1)</div>
                <div class="depth-value" id="water-temp-1">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
            <div class="depth-card">
                <div class="depth-label">Middle (Depth 2)</div>
                <div class="depth-value" id="water-temp-2">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
            <div class="depth-card">
                <div class="depth-label">Bottom (Depth 3)</div>
                <div class="depth-value" id="water-temp-3">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
        </div>

        <!-- Quick Chart Overview -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">24-Hour Overview</h3>
            </div>
            <div style="height: 300px;">
                <canvas id="overview-chart"></canvas>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Load overview chart on page load
        document.addEventListener('DOMContentLoaded', async () => {
            const data = await window.chartManager.loadHistoricalData('temperature', 24);
            if (data.length > 0) {
                window.chartManager.createLineChart('overview-chart', data, 'Air Temperature (°C)', '#667eea');
            }
        });
    </script>
</body>
</html>