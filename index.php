<?php
/**
 * BLIMAS Dashboard - Homepage with Real-time Monitoring
 * Bolgoda Lake Information Monitoring & Analysis System
 */

require_once 'config/config.php';
require_once 'config/database.php';

// Get latest sensor data for initial display
$database = new Database();
$pdo = $database->connect();
$latest_data = null;

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1");
        $stmt->execute();
        $latest_data = $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching latest data: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="breadcrumb">
            <span>Real-time Dashboard</span>
        </div>
    </div>

    <div class="container dashboard-container">
        <!-- Connection Status -->
        <div style="text-align: center; margin-bottom: 20px;">
            <span id="connection-status" class="status-indicator status-good"></span>
            <span id="last-updated">Loading...</span>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Air Temperature Card -->
            <div class="dashboard-card card-temperature">
                <div class="card-header">
                    <h3 class="card-title">Air Temperature</h3>
                    <i class="fa fa-thermometer-half card-icon"></i>
                </div>
                <div class="card-value">
                    <span id="air-temp-value"><?php echo $latest_data ? number_format($latest_data['air_temp'], 1) : '--'; ?></span>
                    <span class="card-unit">°C</span>
                </div>
                <div class="card-status" id="air-temp-status">
                    <span class="status-indicator status-good"></span>Normal
                </div>
            </div>

            <!-- Humidity Card -->
            <div class="dashboard-card card-humidity">
                <div class="card-header">
                    <h3 class="card-title">Humidity</h3>
                    <i class="fa fa-tint card-icon"></i>
                </div>
                <div class="card-value">
                    <span id="humidity-value"><?php echo $latest_data ? number_format($latest_data['humidity'], 1) : '--'; ?></span>
                    <span class="card-unit">%</span>
                </div>
                <div class="card-status" id="humidity-status">
                    <span class="status-indicator status-good"></span>Normal
                </div>
            </div>

            <!-- Water Level Card -->
            <div class="dashboard-card card-water-level">
                <div class="card-header">
                    <h3 class="card-title">Water Level</h3>
                    <i class="fa fa-water card-icon"></i>
                </div>
                <div class="card-value">
                    <span id="water-level-value"><?php echo $latest_data ? number_format($latest_data['water_level'], 1) : '--'; ?></span>
                    <span class="card-unit">cm</span>
                </div>
                <div class="card-status" id="water-level-status">
                    <span class="status-indicator status-good"></span>Normal
                </div>
            </div>

            <!-- Battery Level Card -->
            <div class="dashboard-card card-battery">
                <div class="card-header">
                    <h3 class="card-title">Battery Level</h3>
                    <i class="fa fa-battery-three-quarters card-icon"></i>
                </div>
                <div class="card-value">
                    <span id="battery-level-value"><?php echo $latest_data ? number_format($latest_data['battery_level'], 1) : '--'; ?></span>
                    <span class="card-unit">%</span>
                </div>
                <div class="card-status" id="battery-level-status">
                    <span class="status-indicator status-good"></span>Good
                </div>
            </div>
        </div>

        <!-- Water Temperature Multi-Depth Section -->
        <div class="chart-container">
            <h3 class="chart-title">Water Temperature at Different Depths</h3>
            <div class="dashboard-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="dashboard-card" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Depth 1 (Surface)</h4>
                        <i class="fa fa-thermometer-quarter card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span id="water-temp1-value"><?php echo $latest_data ? number_format($latest_data['water_temp1'], 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #0984e3 0%, #2d3436 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Depth 2 (Middle)</h4>
                        <i class="fa fa-thermometer-half card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span id="water-temp2-value"><?php echo $latest_data ? number_format($latest_data['water_temp2'], 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
                
                <div class="dashboard-card" style="background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);">
                    <div class="card-header">
                        <h4 class="card-title">Depth 3 (Bottom)</h4>
                        <i class="fa fa-thermometer-three-quarters card-icon"></i>
                    </div>
                    <div class="card-value">
                        <span id="water-temp3-value"><?php echo $latest_data ? number_format($latest_data['water_temp3'], 1) : '--'; ?></span>
                        <span class="card-unit">°C</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weather Information Section -->
        <div class="weather-section">
            <div class="weather-header">
                <h3>Weather in <span id="weather-location">Katubedda, Sri Lanka</span></h3>
            </div>
            <div class="weather-current">
                <div class="weather-icon">
                    <img id="weather-icon" src="" alt="Weather Icon" style="width: 80px; height: 80px;">
                </div>
                <div class="weather-main">
                    <div class="weather-temp" id="weather-temp">--°C</div>
                    <div class="weather-condition" id="weather-condition">Loading...</div>
                </div>
                <div class="weather-details">
                    <div class="weather-detail">
                        <span>Humidity:</span>
                        <span id="weather-humidity-val">--%</span>
                    </div>
                    <div class="weather-detail">
                        <span>Wind:</span>
                        <span id="weather-wind-val">-- kph</span>
                    </div>
                    <div class="weather-detail">
                        <span>Pressure:</span>
                        <span id="weather-pressure-val">-- mb</span>
                    </div>
                    <div class="weather-detail">
                        <span>Visibility:</span>
                        <span id="weather-visibility-val">-- km</span>
                    </div>
                    <div class="weather-detail">
                        <span>Feels like:</span>
                        <span id="weather-feels-like-val">--°C</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Navigation -->
        <div class="chart-container">
            <h3 class="chart-title">Detailed Analysis</h3>
            <div class="dashboard-grid">
                <a href="pages/temperature.php" class="dashboard-card" style="text-decoration: none; color: white;">
                    <div class="card-header">
                        <h4 class="card-title">Temperature Analysis</h4>
                        <i class="fa fa-line-chart card-icon"></i>
                    </div>
                    <p>View detailed temperature trends and historical data</p>
                </a>
                
                <a href="pages/humidity.php" class="dashboard-card" style="text-decoration: none; color: white;">
                    <div class="card-header">
                        <h4 class="card-title">Humidity Analysis</h4>
                        <i class="fa fa-area-chart card-icon"></i>
                    </div>
                    <p>Analyze humidity patterns and trends</p>
                </a>
                
                <a href="pages/water-level.php" class="dashboard-card" style="text-decoration: none; color: white;">
                    <div class="card-header">
                        <h4 class="card-title">Water Level Monitoring</h4>
                        <i class="fa fa-bar-chart card-icon"></i>
                    </div>
                    <p>Monitor water level changes over time</p>
                </a>
                
                <a href="pages/water-temperature.php" class="dashboard-card" style="text-decoration: none; color: white;">
                    <div class="card-header">
                        <h4 class="card-title">Water Temperature</h4>
                        <i class="fa fa-thermometer-full card-icon"></i>
                    </div>
                    <p>Multi-depth water temperature analysis</p>
                </a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>