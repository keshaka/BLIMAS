<?php
$page_title = 'Air Temperature Monitoring';
$base_url = '..';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="breadcrumb">
            <a href="../index.php">Home</a>
            <span>Air Temperature</span>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h1>Air Temperature Monitoring</h1>
            <p>Real-time air temperature data from DHT sensor</p>
        </div>
        
        <div class="chart-section">
            <div class="chart-container">
                <h2 class="chart-title">
                    <i class="fa fa-thermometer-half"></i> Air Temperature Over Time
                </h2>
                <div class="chart-wrapper">
                    <canvas id="temperatureChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="current-reading">
            <div class="reading-card">
                <div class="reading-icon">
                    <i class="fa fa-thermometer-half"></i>
                </div>
                <div class="reading-content">
                    <h3>Current Air Temperature</h3>
                    <div class="reading-value" id="currentTemperature">Loading...</div>
                    <div class="reading-time">Last updated: <span id="lastUpdate">Loading...</span></div>
                </div>
            </div>
        </div>
        
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>24h Average</h4>
                    <div class="stat-value" id="avgTemp24h">--</div>
                    <small>°C</small>
                </div>
                <div class="stat-card">
                    <h4>24h Maximum</h4>
                    <div class="stat-value" id="maxTemp24h">--</div>
                    <small>°C</small>
                </div>
                <div class="stat-card">
                    <h4>24h Minimum</h4>
                    <div class="stat-value" id="minTemp24h">--</div>
                    <small>°C</small>
                </div>
                <div class="stat-card">
                    <h4>Data Points</h4>
                    <div class="stat-value" id="dataPoints">--</div>
                    <small>readings</small>
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
    background: linear-gradient(135deg, #e74c3c, #c0392b);
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

.current-reading {
    margin-bottom: 40px;
}

.reading-card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.reading-icon {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 30px;
    font-size: 32px;
}

.reading-content h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 1.5rem;
}

.reading-value {
    font-size: 3rem;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 10px;
}

.reading-time {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.stats-section {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
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
    border-left: 4px solid #e74c3c;
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
    color: #e74c3c;
    margin-bottom: 5px;
}

.stat-card small {
    color: #7f8c8d;
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
    .reading-card {
        flex-direction: column;
        text-align: center;
    }
    
    .reading-icon {
        margin: 0 0 20px 0;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let temperatureChart = null;
    
    // Load chart data
    async function loadTemperatureChart() {
        try {
            const response = await fetch('/api/get-sensor-data.php?limit=50');
            const result = await response.json();
            
            if (result.success) {
                const data = Array.isArray(result.data) ? result.data : [result.data];
                
                // Create chart
                temperatureChart = window.blimasCharts.createTemperatureChart('temperatureChart', data);
                
                // Update current reading
                if (data.length > 0) {
                    const latest = data[0];
                    document.getElementById('currentTemperature').textContent = 
                        latest.air_temperature ? `${latest.air_temperature}°C` : 'N/A';
                    document.getElementById('lastUpdate').textContent = 
                        new Date(latest.timestamp).toLocaleString();
                }
                
                // Calculate statistics
                calculateStats(data);
            } else {
                console.error('Failed to load temperature data:', result.error);
            }
        } catch (error) {
            console.error('Error loading temperature chart:', error);
        }
    }
    
    function calculateStats(data) {
        const temperatures = data
            .map(item => item.air_temperature)
            .filter(temp => temp !== null && temp !== undefined);
        
        if (temperatures.length === 0) return;
        
        const avg = temperatures.reduce((a, b) => a + b, 0) / temperatures.length;
        const max = Math.max(...temperatures);
        const min = Math.min(...temperatures);
        
        document.getElementById('avgTemp24h').textContent = avg.toFixed(1);
        document.getElementById('maxTemp24h').textContent = max.toFixed(1);
        document.getElementById('minTemp24h').textContent = min.toFixed(1);
        document.getElementById('dataPoints').textContent = temperatures.length;
    }
    
    // Load initial data
    loadTemperatureChart();
    
    // Refresh every 30 seconds
    setInterval(loadTemperatureChart, 30000);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>