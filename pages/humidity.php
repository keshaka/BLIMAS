<?php
$page_title = 'Humidity Monitoring';
$base_url = '..';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="breadcrumb">
            <a href="../index.php">Home</a>
            <span>Humidity</span>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h1>Humidity Monitoring</h1>
            <p>Real-time relative humidity data from DHT sensor</p>
        </div>
        
        <div class="chart-section">
            <div class="chart-container">
                <h2 class="chart-title">
                    <i class="fa fa-tint"></i> Humidity Over Time
                </h2>
                <div class="chart-wrapper">
                    <canvas id="humidityChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="current-reading">
            <div class="reading-card">
                <div class="reading-icon">
                    <i class="fa fa-tint"></i>
                </div>
                <div class="reading-content">
                    <h3>Current Humidity</h3>
                    <div class="reading-value" id="currentHumidity">Loading...</div>
                    <div class="reading-time">Last updated: <span id="lastUpdate">Loading...</span></div>
                </div>
            </div>
        </div>
        
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>24h Average</h4>
                    <div class="stat-value" id="avgHumidity24h">--</div>
                    <small>%</small>
                </div>
                <div class="stat-card">
                    <h4>24h Maximum</h4>
                    <div class="stat-value" id="maxHumidity24h">--</div>
                    <small>%</small>
                </div>
                <div class="stat-card">
                    <h4>24h Minimum</h4>
                    <div class="stat-value" id="minHumidity24h">--</div>
                    <small>%</small>
                </div>
                <div class="stat-card">
                    <h4>Comfort Level</h4>
                    <div class="stat-value" id="comfortLevel">--</div>
                    <small>status</small>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-card">
                <h3><i class="fa fa-info-circle"></i> Humidity Guidelines</h3>
                <div class="humidity-guide">
                    <div class="guide-item">
                        <div class="guide-range" style="background: #e74c3c;">0-30%</div>
                        <div class="guide-desc">Too Dry - May cause discomfort</div>
                    </div>
                    <div class="guide-item">
                        <div class="guide-range" style="background: #27ae60;">30-60%</div>
                        <div class="guide-desc">Optimal - Comfortable conditions</div>
                    </div>
                    <div class="guide-item">
                        <div class="guide-range" style="background: #f39c12;">60-80%</div>
                        <div class="guide-desc">High - Feels muggy</div>
                    </div>
                    <div class="guide-item">
                        <div class="guide-range" style="background: #e74c3c;">80-100%</div>
                        <div class="guide-desc">Very High - Oppressive conditions</div>
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
    background: linear-gradient(135deg, #3498db, #2980b9);
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
    background: linear-gradient(135deg, #3498db, #2980b9);
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
    color: #3498db;
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
    border-left: 4px solid #3498db;
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
    color: #3498db;
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

.humidity-guide {
    display: grid;
    gap: 15px;
}

.guide-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.guide-range {
    min-width: 80px;
    padding: 8px 12px;
    color: white;
    font-weight: 500;
    border-radius: 5px;
    text-align: center;
    margin-right: 15px;
}

.guide-desc {
    color: #2c3e50;
    font-weight: 500;
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
    
    .guide-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .guide-range {
        margin: 0 0 10px 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let humidityChart = null;
    
    // Load chart data
    async function loadHumidityChart() {
        try {
            const response = await fetch('/api/get-sensor-data.php?limit=50');
            const result = await response.json();
            
            if (result.success) {
                const data = Array.isArray(result.data) ? result.data : [result.data];
                
                // Create chart
                humidityChart = window.blimasCharts.createHumidityChart('humidityChart', data);
                
                // Update current reading
                if (data.length > 0) {
                    const latest = data[0];
                    const humidity = latest.humidity;
                    
                    document.getElementById('currentHumidity').textContent = 
                        humidity ? `${humidity}%` : 'N/A';
                    document.getElementById('lastUpdate').textContent = 
                        new Date(latest.timestamp).toLocaleString();
                    
                    // Update comfort level
                    updateComfortLevel(humidity);
                }
                
                // Calculate statistics
                calculateStats(data);
            } else {
                console.error('Failed to load humidity data:', result.error);
            }
        } catch (error) {
            console.error('Error loading humidity chart:', error);
        }
    }
    
    function calculateStats(data) {
        const humidities = data
            .map(item => item.humidity)
            .filter(humidity => humidity !== null && humidity !== undefined);
        
        if (humidities.length === 0) return;
        
        const avg = humidities.reduce((a, b) => a + b, 0) / humidities.length;
        const max = Math.max(...humidities);
        const min = Math.min(...humidities);
        
        document.getElementById('avgHumidity24h').textContent = avg.toFixed(1);
        document.getElementById('maxHumidity24h').textContent = max.toFixed(1);
        document.getElementById('minHumidity24h').textContent = min.toFixed(1);
    }
    
    function updateComfortLevel(humidity) {
        const comfortElement = document.getElementById('comfortLevel');
        
        if (humidity === null || humidity === undefined) {
            comfortElement.textContent = 'N/A';
            comfortElement.style.color = '#7f8c8d';
            return;
        }
        
        let level, color;
        
        if (humidity < 30) {
            level = 'Too Dry';
            color = '#e74c3c';
        } else if (humidity <= 60) {
            level = 'Optimal';
            color = '#27ae60';
        } else if (humidity <= 80) {
            level = 'High';
            color = '#f39c12';
        } else {
            level = 'Very High';
            color = '#e74c3c';
        }
        
        comfortElement.textContent = level;
        comfortElement.style.color = color;
    }
    
    // Load initial data
    loadHumidityChart();
    
    // Refresh every 30 seconds
    setInterval(loadHumidityChart, 30000);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>