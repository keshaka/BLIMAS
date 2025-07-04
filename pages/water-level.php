<?php
$page_title = 'Water Level Monitoring';
$base_url = '..';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="breadcrumb">
            <a href="../index.php">Home</a>
            <span>Water Level</span>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h1>Water Level Monitoring</h1>
            <p>Real-time water level measurements using ultrasonic sensor</p>
        </div>
        
        <div class="chart-section">
            <div class="chart-container">
                <h2 class="chart-title">
                    <i class="fa fa-bar-chart"></i> Water Level Over Time
                </h2>
                <div class="chart-wrapper">
                    <canvas id="waterLevelChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="current-reading">
            <div class="reading-card">
                <div class="reading-icon">
                    <i class="fa fa-bar-chart"></i>
                </div>
                <div class="reading-content">
                    <h3>Current Water Level</h3>
                    <div class="reading-value" id="currentWaterLevel">Loading...</div>
                    <div class="reading-subtitle">Distance from sensor</div>
                    <div class="reading-time">Last updated: <span id="lastUpdate">Loading...</span></div>
                </div>
            </div>
        </div>
        
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>24h Average</h4>
                    <div class="stat-value" id="avgLevel24h">--</div>
                    <small>cm</small>
                </div>
                <div class="stat-card">
                    <h4>24h Maximum</h4>
                    <div class="stat-value" id="maxLevel24h">--</div>
                    <small>cm</small>
                </div>
                <div class="stat-card">
                    <h4>24h Minimum</h4>
                    <div class="stat-value" id="minLevel24h">--</div>
                    <small>cm</small>
                </div>
                <div class="stat-card">
                    <h4>Level Trend</h4>
                    <div class="stat-value" id="levelTrend">--</div>
                    <small>status</small>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-card">
                <h3><i class="fa fa-info-circle"></i> About Water Level Monitoring</h3>
                <div class="info-content">
                    <p>
                        The water level is measured using an ultrasonic sensor that calculates the distance 
                        from the sensor to the water surface. A smaller distance value indicates higher water level.
                    </p>
                    <div class="level-indicators">
                        <h4>Water Level Indicators:</h4>
                        <div class="indicator-grid">
                            <div class="indicator-item">
                                <div class="indicator-color" style="background: #e74c3c;"></div>
                                <span>Very Low (&gt;200cm)</span>
                            </div>
                            <div class="indicator-item">
                                <div class="indicator-color" style="background: #f39c12;"></div>
                                <span>Low (150-200cm)</span>
                            </div>
                            <div class="indicator-item">
                                <div class="indicator-color" style="background: #27ae60;"></div>
                                <span>Normal (100-150cm)</span>
                            </div>
                            <div class="indicator-item">
                                <div class="indicator-color" style="background: #3498db;"></div>
                                <span>High (&lt;100cm)</span>
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
    background: linear-gradient(135deg, #2ecc71, #27ae60);
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
    background: linear-gradient(135deg, #2ecc71, #27ae60);
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
    color: #2ecc71;
    margin-bottom: 5px;
}

.reading-subtitle {
    color: #7f8c8d;
    font-size: 0.9rem;
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
    border-left: 4px solid #2ecc71;
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
    color: #2ecc71;
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

.level-indicators h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.indicator-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.indicator-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.indicator-color {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 10px;
}

.indicator-item span {
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
    
    .stats-grid, .indicator-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let waterLevelChart = null;
    
    // Load chart data
    async function loadWaterLevelChart() {
        try {
            const response = await fetch('/api/get-sensor-data.php?limit=50');
            const result = await response.json();
            
            if (result.success) {
                const data = Array.isArray(result.data) ? result.data : [result.data];
                
                // Create chart
                waterLevelChart = window.blimasCharts.createWaterLevelChart('waterLevelChart', data);
                
                // Update current reading
                if (data.length > 0) {
                    const latest = data[0];
                    const waterLevel = latest.water_level;
                    
                    document.getElementById('currentWaterLevel').textContent = 
                        waterLevel ? `${waterLevel} cm` : 'N/A';
                    document.getElementById('lastUpdate').textContent = 
                        new Date(latest.timestamp).toLocaleString();
                    
                    // Update reading value color based on level
                    updateLevelColor(waterLevel);
                }
                
                // Calculate statistics
                calculateStats(data);
            } else {
                console.error('Failed to load water level data:', result.error);
            }
        } catch (error) {
            console.error('Error loading water level chart:', error);
        }
    }
    
    function calculateStats(data) {
        const levels = data
            .map(item => item.water_level)
            .filter(level => level !== null && level !== undefined);
        
        if (levels.length === 0) return;
        
        const avg = levels.reduce((a, b) => a + b, 0) / levels.length;
        const max = Math.max(...levels);
        const min = Math.min(...levels);
        
        document.getElementById('avgLevel24h').textContent = avg.toFixed(1);
        document.getElementById('maxLevel24h').textContent = max.toFixed(1);
        document.getElementById('minLevel24h').textContent = min.toFixed(1);
        
        // Calculate trend
        if (levels.length >= 2) {
            const recent = levels.slice(0, Math.min(5, levels.length));
            const older = levels.slice(-Math.min(5, levels.length));
            
            const recentAvg = recent.reduce((a, b) => a + b, 0) / recent.length;
            const olderAvg = older.reduce((a, b) => a + b, 0) / older.length;
            
            updateTrend(recentAvg - olderAvg);
        }
    }
    
    function updateLevelColor(level) {
        const readingValue = document.getElementById('currentWaterLevel');
        
        if (level === null || level === undefined) {
            readingValue.style.color = '#7f8c8d';
            return;
        }
        
        let color;
        if (level > 200) {
            color = '#e74c3c'; // Very Low (red)
        } else if (level > 150) {
            color = '#f39c12'; // Low (orange)
        } else if (level > 100) {
            color = '#27ae60'; // Normal (green)
        } else {
            color = '#3498db'; // High (blue)
        }
        
        readingValue.style.color = color;
    }
    
    function updateTrend(difference) {
        const trendElement = document.getElementById('levelTrend');
        
        let trend, color;
        
        if (Math.abs(difference) < 1) {
            trend = 'Stable';
            color = '#27ae60';
        } else if (difference > 0) {
            trend = 'Rising';
            color = '#3498db';
        } else {
            trend = 'Falling';
            color = '#e74c3c';
        }
        
        trendElement.textContent = trend;
        trendElement.style.color = color;
    }
    
    // Load initial data
    loadWaterLevelChart();
    
    // Refresh every 30 seconds
    setInterval(loadWaterLevelChart, 30000);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>