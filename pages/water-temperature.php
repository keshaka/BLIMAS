<?php
$page_title = 'Water Temperature Monitoring';
$base_url = '..';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="breadcrumb">
            <a href="../index.php">Home</a>
            <span>Water Temperature</span>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h1>Water Temperature Monitoring</h1>
            <p>Multi-depth water temperature measurements from DS18B20 sensors</p>
        </div>
        
        <div class="chart-section">
            <div class="chart-container">
                <h2 class="chart-title">
                    <i class="fa fa-thermometer"></i> Water Temperature by Depth
                </h2>
                <div class="chart-wrapper">
                    <canvas id="waterTemperatureChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="current-readings">
            <div class="readings-grid">
                <div class="depth-card depth-1">
                    <div class="depth-icon">
                        <span>1</span>
                    </div>
                    <div class="depth-content">
                        <h3>Depth 1 (Surface)</h3>
                        <div class="depth-value" id="temp1">Loading...</div>
                        <small>Surface temperature</small>
                    </div>
                </div>
                
                <div class="depth-card depth-2">
                    <div class="depth-icon">
                        <span>2</span>
                    </div>
                    <div class="depth-content">
                        <h3>Depth 2 (Middle)</h3>
                        <div class="depth-value" id="temp2">Loading...</div>
                        <small>Mid-level temperature</small>
                    </div>
                </div>
                
                <div class="depth-card depth-3">
                    <div class="depth-icon">
                        <span>3</span>
                    </div>
                    <div class="depth-content">
                        <h3>Depth 3 (Bottom)</h3>
                        <div class="depth-value" id="temp3">Loading...</div>
                        <small>Bottom temperature</small>
                    </div>
                </div>
            </div>
            
            <div class="average-temp">
                <div class="avg-card">
                    <div class="avg-icon">
                        <i class="fa fa-calculator"></i>
                    </div>
                    <div class="avg-content">
                        <h3>Average Temperature</h3>
                        <div class="avg-value" id="avgTemp">Loading...</div>
                        <div class="update-time">Last updated: <span id="lastUpdate">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>24h Avg (Surface)</h4>
                    <div class="stat-value" id="avgTemp1_24h">--</div>
                    <small>°C</small>
                </div>
                <div class="stat-card">
                    <h4>24h Avg (Middle)</h4>
                    <div class="stat-value" id="avgTemp2_24h">--</div>
                    <small>°C</small>
                </div>
                <div class="stat-card">
                    <h4>24h Avg (Bottom)</h4>
                    <div class="stat-value" id="avgTemp3_24h">--</div>
                    <small>°C</small>
                </div>
                <div class="stat-card">
                    <h4>Temperature Range</h4>
                    <div class="stat-value" id="tempRange">--</div>
                    <small>°C spread</small>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-card">
                <h3><i class="fa fa-info-circle"></i> Water Temperature Analysis</h3>
                <div class="info-content">
                    <p>
                        Water temperature varies with depth due to thermal stratification. Surface water is typically 
                        warmer during the day and cooler at night, while deeper water maintains more stable temperatures.
                    </p>
                    <div class="thermal-info">
                        <h4>Thermal Layers:</h4>
                        <ul>
                            <li><strong>Epilimnion (Surface):</strong> Warm, well-mixed layer affected by weather</li>
                            <li><strong>Metalimnion (Middle):</strong> Transition zone with rapid temperature change</li>
                            <li><strong>Hypolimnion (Bottom):</strong> Cool, dense water with stable temperature</li>
                        </ul>
                    </div>
                    <div class="quality-indicators">
                        <h4>Water Quality Indicators:</h4>
                        <div class="quality-grid">
                            <div class="quality-item">
                                <div class="quality-color" style="background: #3498db;"></div>
                                <span>Cool (&lt;20°C) - High dissolved oxygen</span>
                            </div>
                            <div class="quality-item">
                                <div class="quality-color" style="background: #27ae60;"></div>
                                <span>Moderate (20-25°C) - Good for aquatic life</span>
                            </div>
                            <div class="quality-item">
                                <div class="quality-color" style="background: #f39c12;"></div>
                                <span>Warm (25-30°C) - Reduced oxygen levels</span>
                            </div>
                            <div class="quality-item">
                                <div class="quality-color" style="background: #e74c3c;"></div>
                                <span>Hot (&gt;30°C) - Stress on aquatic ecosystem</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.page-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 30px 0;
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
    color: white;
    border-radius: 10px;
}

.page-header h1 {
    margin: 0 0 10px 0;
    font-size: 2.5rem;
    font-weight: 300;
}

.page-header p {
    margin: 0;
    font-size: 1.1rem;
    opacity: 0.9;
}

.chart-section {
    margin-bottom: 40px;
}

.chart-container {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.chart-title {
    margin: 0 0 30px 0;
    color: #2c3e50;
    font-size: 1.8rem;
    font-weight: 500;
}

.chart-wrapper {
    width: 100%;
    height: 400px;
    position: relative;
}

.current-readings {
    margin-bottom: 40px;
}

.readings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.depth-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
}

.depth-1 { border-left: 4px solid #e74c3c; }
.depth-2 { border-left: 4px solid #f39c12; }
.depth-3 { border-left: 4px solid #27ae60; }

.depth-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    font-size: 20px;
    font-weight: 700;
    color: white;
}

.depth-1 .depth-icon { background: #e74c3c; }
.depth-2 .depth-icon { background: #f39c12; }
.depth-3 .depth-icon { background: #27ae60; }

.depth-content h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1.2rem;
    font-weight: 500;
}

.depth-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.depth-1 .depth-value { color: #e74c3c; }
.depth-2 .depth-value { color: #f39c12; }
.depth-3 .depth-value { color: #27ae60; }

.depth-content small {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.average-temp {
    display: flex;
    justify-content: center;
}

.avg-card {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
    color: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(155, 89, 182, 0.3);
    display: flex;
    align-items: center;
    min-width: 350px;
}

.avg-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 25px;
    font-size: 24px;
}

.avg-content h3 {
    margin: 0 0 10px 0;
    font-size: 1.3rem;
    font-weight: 500;
}

.avg-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.update-time {
    font-size: 0.9rem;
    opacity: 0.9;
}

.stats-section {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 40px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #9b59b6;
}

.stat-card h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1rem;
    font-weight: 500;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #9b59b6;
    margin-bottom: 5px;
}

.stat-card small {
    color: #7f8c8d;
    font-size: 0.8rem;
}

.info-section {
    margin-bottom: 40px;
}

.info-card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.info-card h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 1.5rem;
}

.info-content p {
    color: #7f8c8d;
    line-height: 1.6;
    margin-bottom: 20px;
}

.thermal-info h4, .quality-indicators h4 {
    margin: 20px 0 15px 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.thermal-info ul {
    color: #7f8c8d;
    line-height: 1.6;
}

.thermal-info li {
    margin-bottom: 8px;
}

.quality-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.quality-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.quality-color {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 10px;
}

.quality-item span {
    color: #2c3e50;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .readings-grid {
        grid-template-columns: 1fr;
    }
    
    .depth-card {
        text-align: center;
        flex-direction: column;
    }
    
    .depth-icon {
        margin: 0 0 15px 0;
    }
    
    .avg-card {
        flex-direction: column;
        text-align: center;
        min-width: auto;
    }
    
    .avg-icon {
        margin: 0 0 20px 0;
    }
    
    .stats-grid, .quality-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let waterTemperatureChart = null;
    
    // Load chart data
    async function loadWaterTemperatureChart() {
        try {
            const response = await fetch('/api/get-sensor-data.php?limit=50');
            const result = await response.json();
            
            if (result.success) {
                const data = Array.isArray(result.data) ? result.data : [result.data];
                
                // Create chart
                waterTemperatureChart = window.blimasCharts.createWaterTemperatureChart('waterTemperatureChart', data);
                
                // Update current readings
                if (data.length > 0) {
                    const latest = data[0];
                    const temps = latest.water_temperatures;
                    
                    if (temps) {
                        document.getElementById('temp1').textContent = 
                            temps.depth1 ? `${temps.depth1}°C` : 'N/A';
                        document.getElementById('temp2').textContent = 
                            temps.depth2 ? `${temps.depth2}°C` : 'N/A';
                        document.getElementById('temp3').textContent = 
                            temps.depth3 ? `${temps.depth3}°C` : 'N/A';
                        
                        // Calculate and display average
                        const validTemps = [temps.depth1, temps.depth2, temps.depth3]
                            .filter(t => t !== null && t !== undefined);
                        
                        if (validTemps.length > 0) {
                            const avg = validTemps.reduce((a, b) => a + b, 0) / validTemps.length;
                            document.getElementById('avgTemp').textContent = `${avg.toFixed(1)}°C`;
                        } else {
                            document.getElementById('avgTemp').textContent = 'N/A';
                        }
                    }
                    
                    document.getElementById('lastUpdate').textContent = 
                        new Date(latest.timestamp).toLocaleString();
                }
                
                // Calculate statistics
                calculateStats(data);
            } else {
                console.error('Failed to load water temperature data:', result.error);
            }
        } catch (error) {
            console.error('Error loading water temperature chart:', error);
        }
    }
    
    function calculateStats(data) {
        const depth1Temps = data.map(item => item.water_temperatures?.depth1).filter(t => t !== null && t !== undefined);
        const depth2Temps = data.map(item => item.water_temperatures?.depth2).filter(t => t !== null && t !== undefined);
        const depth3Temps = data.map(item => item.water_temperatures?.depth3).filter(t => t !== null && t !== undefined);
        
        // Calculate averages
        if (depth1Temps.length > 0) {
            const avg1 = depth1Temps.reduce((a, b) => a + b, 0) / depth1Temps.length;
            document.getElementById('avgTemp1_24h').textContent = avg1.toFixed(1);
        }
        
        if (depth2Temps.length > 0) {
            const avg2 = depth2Temps.reduce((a, b) => a + b, 0) / depth2Temps.length;
            document.getElementById('avgTemp2_24h').textContent = avg2.toFixed(1);
        }
        
        if (depth3Temps.length > 0) {
            const avg3 = depth3Temps.reduce((a, b) => a + b, 0) / depth3Temps.length;
            document.getElementById('avgTemp3_24h').textContent = avg3.toFixed(1);
        }
        
        // Calculate temperature range
        const allTemps = [...depth1Temps, ...depth2Temps, ...depth3Temps];
        if (allTemps.length > 0) {
            const max = Math.max(...allTemps);
            const min = Math.min(...allTemps);
            const range = max - min;
            document.getElementById('tempRange').textContent = range.toFixed(1);
        }
    }
    
    // Load initial data
    loadWaterTemperatureChart();
    
    // Refresh every 30 seconds
    setInterval(loadWaterTemperatureChart, 30000);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>