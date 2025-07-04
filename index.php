<?php
$page_title = 'Home Dashboard';
$base_url = '';
include __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <!-- Weather Information Section -->
    <div class="forecast-table">
        <div class="container">
            <div class="forecast-container">
                <div class="today forecast">
                    <div class="forecast-header">
                        <div class="day" id="date">Loading...</div>
                        <div class="date" id="time">Loading...</div>
                    </div> <!-- .forecast-header -->
                    <div class="forecast-content">
                        <div class="location">Katubedda, Sri Lanka</div>
                        <div class="degree">
                            <div class="num"><span id="temperature">Loading...</span></div>
                        </div>
                        <span><img src="images/hum.png" alt="Humidity"><span id="weatherHumidity">Loading...</span></span>
                        <span><img src="images/icon-wind.png" alt="Wind Speed"><span id="wind">Loading...</span></span>
                        <span><img src="images/icon-compass.png" alt="Condition"><span id="condition">Loading...</span></span>
                        <span><img src="images/rain.png" alt="Cloud Coverage"><span id="rain">Loading...</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sensor Data Dashboard -->
    <div class="fullwidth-block">
        <div class="container">
            <h2 class="section-title">Real-time Sensor Data</h2>
            
            <div class="image-container">
                <img src="/images/a.png" alt="Bolgoda Lake Monitoring System">
                <a href="pages/water-level.php" class="link" data-hover="Water Level">
                    <div class="sensor-value" id="distance">Loading...</div>
                </a>
                <a href="pages/temperature.php" class="linkut" data-hover="Air Temperature">
                    <div class="sensor-value" id="tempDHT">Loading...</div>
                </a>
                <a href="pages/water-temperature.php" class="link3" data-hover="Water Temperature (Depth 3)">
                    <div class="sensor-value" id="temp3">Loading...</div>
                </a>
                <a href="pages/water-temperature.php" class="link2" data-hover="Water Temperature (Depth 2)">
                    <div class="sensor-value" id="temp2">Loading...</div>
                </a>
                <a href="pages/water-temperature.php" class="link1" data-hover="Water Temperature (Depth 1)">
                    <div class="sensor-value" id="temp1">Loading...</div>
                </a>
                <a href="pages/humidity.php" class="linkh" data-hover="Humidity">
                    <div class="sensor-value" id="humhum">Loading...</div>
                </a>
            </div>
            
            <!-- Data Summary Cards -->
            <div class="data-summary">
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="card-icon">
                            <i class="fa fa-thermometer-half"></i>
                        </div>
                        <div class="card-content">
                            <h3>Air Temperature</h3>
                            <div class="card-value" id="airTempSummary">Loading...</div>
                            <small>Current reading</small>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="card-icon">
                            <i class="fa fa-tint"></i>
                        </div>
                        <div class="card-content">
                            <h3>Humidity</h3>
                            <div class="card-value" id="humiditySummary">Loading...</div>
                            <small>Relative humidity</small>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="card-icon">
                            <i class="fa fa-bar-chart"></i>
                        </div>
                        <div class="card-content">
                            <h3>Water Level</h3>
                            <div class="card-value" id="waterLevelSummary">Loading...</div>
                            <small>Distance from sensor</small>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="card-icon">
                            <i class="fa fa-thermometer"></i>
                        </div>
                        <div class="card-content">
                            <h3>Water Temperature</h3>
                            <div class="card-value" id="waterTempSummary">Loading...</div>
                            <small>Average of all depths</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Last Update Information -->
            <div class="update-info">
                <p>
                    <i class="fa fa-clock-o"></i> 
                    Last Updated: <span id="lastUpdate">Loading...</span>
                    <span class="update-status" id="updateStatus">
                        <i class="fa fa-circle" style="color: #27ae60;"></i> Live
                    </span>
                </p>
            </div>
        </div>
    </div>
</main>

<style>
/* Additional styles for the dashboard */
.data-summary {
    margin-top: 40px;
    padding: 30px 0;
    background: #f8f9fa;
    border-radius: 10px;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 0 20px;
}

.summary-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    transition: transform 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-5px);
}

.summary-card .card-icon {
    background: linear-gradient(135deg, #3498db, #2c3e50);
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    font-size: 24px;
}

.summary-card .card-content h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #2c3e50;
    font-weight: 500;
}

.summary-card .card-value {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.summary-card small {
    color: #7f8c8d;
    font-size: 12px;
}

.update-info {
    text-align: center;
    margin-top: 30px;
    padding: 20px;
    background: #ecf0f1;
    border-radius: 5px;
}

.update-info p {
    margin: 0;
    color: #7f8c8d;
}

.update-status {
    margin-left: 15px;
}

.loading {
    color: #f39c12;
}

.error {
    color: #e74c3c;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .summary-cards {
        grid-template-columns: 1fr;
    }
    
    .summary-card {
        text-align: center;
        flex-direction: column;
    }
    
    .summary-card .card-icon {
        margin: 0 0 15px 0;
    }
}
</style>

<script>
// Enhanced dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update summary cards when sensor data is loaded
    if (window.blimas) {
        const originalUpdateSensorDisplay = window.blimas.updateSensorDisplay;
        
        window.blimas.updateSensorDisplay = function() {
            originalUpdateSensorDisplay.call(this);
            
            const data = this.sensors.current;
            if (data) {
                // Update summary cards
                document.getElementById('airTempSummary').textContent = 
                    data.air_temperature ? `${data.air_temperature}°C` : 'N/A';
                
                document.getElementById('humiditySummary').textContent = 
                    data.humidity ? `${data.humidity}%` : 'N/A';
                
                document.getElementById('waterLevelSummary').textContent = 
                    data.water_level ? `${data.water_level} cm` : 'N/A';
                
                // Calculate average water temperature
                if (data.water_temperatures) {
                    const temps = [
                        data.water_temperatures.depth1,
                        data.water_temperatures.depth2,
                        data.water_temperatures.depth3
                    ].filter(t => t !== null && t !== undefined);
                    
                    if (temps.length > 0) {
                        const avgTemp = temps.reduce((a, b) => a + b, 0) / temps.length;
                        document.getElementById('waterTempSummary').textContent = 
                            `${avgTemp.toFixed(1)}°C`;
                    } else {
                        document.getElementById('waterTempSummary').textContent = 'N/A';
                    }
                }
            }
        };
        
        // Update weather humidity separately
        const originalUpdateWeatherDisplay = window.blimas.updateWeatherDisplay;
        
        window.blimas.updateWeatherDisplay = function() {
            originalUpdateWeatherDisplay.call(this);
            
            const weather = this.weather.current;
            if (weather) {
                document.getElementById('weatherHumidity').textContent = `${weather.humidity}%`;
            }
        };
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>