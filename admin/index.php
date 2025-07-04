<?php
$page_title = 'Admin Dashboard';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-content">
    <div class="dashboard-header">
        <h1><i class="fa fa-dashboard"></i> BLIMAS Admin Dashboard</h1>
        <p>Complete system monitoring and management</p>
    </div>
    
    <!-- System Status Cards -->
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fa fa-thermometer-half"></i>
                </div>
                <h3 class="card-title">Air Temperature</h3>
            </div>
            <div class="card-value" id="airTemp">Loading...</div>
            <div class="card-unit">°C</div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fa fa-tint"></i>
                </div>
                <h3 class="card-title">Humidity</h3>
            </div>
            <div class="card-value" id="humidity">Loading...</div>
            <div class="card-unit">%</div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fa fa-bar-chart"></i>
                </div>
                <h3 class="card-title">Water Level</h3>
            </div>
            <div class="card-value" id="waterLevel">Loading...</div>
            <div class="card-unit">cm</div>
        </div>
        
        <div class="dashboard-card battery-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fa fa-battery-three-quarters"></i>
                </div>
                <h3 class="card-title">Battery Level</h3>
            </div>
            <div class="card-value" id="batteryLevel">Loading...</div>
            <div class="card-unit">%</div>
            <div class="battery-indicator">
                <div class="battery-bar">
                    <div class="battery-fill" id="batteryFill"></div>
                </div>
                <span class="card-status" id="batteryStatus">Unknown</span>
            </div>
        </div>
    </div>
    
    <!-- Water Temperature Sensors -->
    <div class="water-temp-section">
        <h2><i class="fa fa-thermometer"></i> Water Temperature Sensors</h2>
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <span>1</span>
                    </div>
                    <h3 class="card-title">Depth 1 (Surface)</h3>
                </div>
                <div class="card-value" id="waterTemp1">Loading...</div>
                <div class="card-unit">°C</div>
            </div>
            
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <span>2</span>
                    </div>
                    <h3 class="card-title">Depth 2 (Middle)</h3>
                </div>
                <div class="card-value" id="waterTemp2">Loading...</div>
                <div class="card-unit">°C</div>
            </div>
            
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon" style="background: linear-gradient(135deg, #27ae60, #229954);">
                        <span>3</span>
                    </div>
                    <h3 class="card-title">Depth 3 (Bottom)</h3>
                </div>
                <div class="card-value" id="waterTemp3">Loading...</div>
                <div class="card-unit">°C</div>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-container">
            <h2 class="chart-title">
                <i class="fa fa-battery-half"></i> Battery Level Trend (24 hours)
            </h2>
            <canvas id="batteryChart"></canvas>
        </div>
    </div>
    
    <!-- System Statistics -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card">
                <h4><i class="fa fa-database"></i> Records (24h)</h4>
                <div class="stat-value" id="records24h">--</div>
                <small>data points</small>
            </div>
            <div class="stat-card">
                <h4><i class="fa fa-clock-o"></i> Last Update</h4>
                <div class="stat-value" id="lastDataUpdate">--</div>
                <small>timestamp</small>
            </div>
            <div class="stat-card">
                <h4><i class="fa fa-signal"></i> Data Quality</h4>
                <div class="stat-value" id="dataQuality">--</div>
                <small>status</small>
            </div>
            <div class="stat-card">
                <h4><i class="fa fa-battery-quarter"></i> Avg Battery</h4>
                <div class="stat-value" id="avgBattery24h">--</div>
                <small>% (24h)</small>
            </div>
        </div>
    </div>
    
    <!-- Recent Data Table -->
    <div class="data-table">
        <h2><i class="fa fa-table"></i> Recent Sensor Data</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Air Temp (°C)</th>
                        <th>Humidity (%)</th>
                        <th>Water Level (cm)</th>
                        <th>Water Temp 1 (°C)</th>
                        <th>Water Temp 2 (°C)</th>
                        <th>Water Temp 3 (°C)</th>
                        <th>Battery (%)</th>
                    </tr>
                </thead>
                <tbody id="dataTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center;">Loading data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- System Actions -->
    <div class="actions-section">
        <h2><i class="fa fa-cogs"></i> System Actions</h2>
        <div class="action-buttons">
            <button class="action-btn" onclick="refreshData()">
                <i class="fa fa-refresh"></i> Refresh Data
            </button>
            <button class="action-btn" onclick="exportData()">
                <i class="fa fa-download"></i> Export Data
            </button>
            <a href="manage-data.php" class="action-btn">
                <i class="fa fa-database"></i> Manage Data
            </a>
            <button class="action-btn warning" onclick="testAlert()">
                <i class="fa fa-exclamation-triangle"></i> Test Alerts
            </button>
        </div>
    </div>
</div>

<style>
.admin-content {
    padding: 20px;
}

.dashboard-header {
    margin-bottom: 30px;
    text-align: center;
}

.dashboard-header h1 {
    color: #2c3e50;
    margin: 0 0 10px 0;
    font-size: 2.5rem;
    font-weight: 300;
}

.dashboard-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
    margin: 0;
}

.water-temp-section {
    margin: 40px 0;
}

.water-temp-section h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.8rem;
    font-weight: 500;
}

.charts-section {
    margin: 40px 0;
}

.stats-section {
    margin: 40px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    text-align: center;
    border-left: 4px solid #3498db;
}

.stat-card h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 1rem;
    font-weight: 500;
}

.stat-card .stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #3498db;
    margin-bottom: 5px;
}

.data-table {
    margin: 40px 0;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.data-table h2 {
    color: #2c3e50;
    padding: 25px;
    margin: 0;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    font-size: 1.5rem;
    font-weight: 500;
}

.table-container {
    overflow-x: auto;
}

.data-table table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.data-table th,
.data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 500;
    color: #2c3e50;
    white-space: nowrap;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.actions-section {
    margin: 40px 0;
}

.actions-section h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.8rem;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.action-btn {
    padding: 12px 20px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.action-btn.warning {
    background: #f39c12;
}

.action-btn.warning:hover {
    background: #e67e22;
}

.action-btn i {
    margin-right: 8px;
}

/* Battery specific styles */
.battery-card .card-value {
    margin-bottom: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-content {
        padding: 15px;
    }
    
    .dashboard-header h1 {
        font-size: 2rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-btn {
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let batteryChart = null;
    
    // Load admin dashboard data
    async function loadDashboardData() {
        try {
            const response = await fetch('/api/admin-data.php?limit=1');
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                updateDashboardCards(data);
                updateSystemStats(result.statistics);
                
                // Load recent data for table
                loadRecentData();
                
                // Load battery chart
                loadBatteryChart();
            } else {
                console.error('Failed to load dashboard data:', result.error);
                showAlert('Failed to load dashboard data', 'error');
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            showAlert('Network error loading dashboard data', 'error');
        }
    }
    
    function updateDashboardCards(data) {
        // Update main sensor cards
        document.getElementById('airTemp').textContent = 
            data.air_temperature ? data.air_temperature.toFixed(1) : 'N/A';
        document.getElementById('humidity').textContent = 
            data.humidity ? data.humidity.toFixed(1) : 'N/A';
        document.getElementById('waterLevel').textContent = 
            data.water_level ? data.water_level.toFixed(1) : 'N/A';
        
        // Update water temperature sensors
        if (data.water_temperatures) {
            document.getElementById('waterTemp1').textContent = 
                data.water_temperatures.depth1 ? data.water_temperatures.depth1.toFixed(1) : 'N/A';
            document.getElementById('waterTemp2').textContent = 
                data.water_temperatures.depth2 ? data.water_temperatures.depth2.toFixed(1) : 'N/A';
            document.getElementById('waterTemp3').textContent = 
                data.water_temperatures.depth3 ? data.water_temperatures.depth3.toFixed(1) : 'N/A';
        }
        
        // Update battery information
        if (data.battery) {
            const batteryLevel = data.battery.level;
            const batteryStatus = data.battery.status;
            
            document.getElementById('batteryLevel').textContent = 
                batteryLevel ? batteryLevel.toFixed(1) : 'N/A';
            
            // Update battery bar
            const batteryFill = document.getElementById('batteryFill');
            const batteryStatusEl = document.getElementById('batteryStatus');
            
            if (batteryLevel) {
                batteryFill.style.width = `${batteryLevel}%`;
                batteryFill.className = `battery-fill ${batteryStatus}`;
                batteryStatusEl.textContent = batteryStatus.charAt(0).toUpperCase() + batteryStatus.slice(1);
                batteryStatusEl.className = `card-status status-${batteryStatus}`;
            }
        }
    }
    
    function updateSystemStats(stats) {
        if (stats) {
            document.getElementById('records24h').textContent = stats.records_last_24h || '0';
            document.getElementById('lastDataUpdate').textContent = 
                stats.last_update ? new Date(stats.last_update).toLocaleString() : 'Unknown';
            document.getElementById('avgBattery24h').textContent = 
                stats.avg_battery_24h ? stats.avg_battery_24h.toFixed(1) : 'N/A';
            
            // Calculate data quality based on recent records
            const quality = stats.records_last_24h > 1000 ? 'Excellent' : 
                           stats.records_last_24h > 500 ? 'Good' : 
                           stats.records_last_24h > 100 ? 'Fair' : 'Poor';
            document.getElementById('dataQuality').textContent = quality;
        }
    }
    
    async function loadRecentData() {
        try {
            const response = await fetch('/api/admin-data.php?limit=10');
            const result = await response.json();
            
            if (result.success) {
                const data = Array.isArray(result.data) ? result.data : [result.data];
                updateDataTable(data);
            }
        } catch (error) {
            console.error('Error loading recent data:', error);
        }
    }
    
    function updateDataTable(data) {
        const tbody = document.getElementById('dataTableBody');
        tbody.innerHTML = '';
        
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${new Date(row.timestamp).toLocaleString()}</td>
                <td>${row.air_temperature ? row.air_temperature.toFixed(1) : 'N/A'}</td>
                <td>${row.humidity ? row.humidity.toFixed(1) : 'N/A'}</td>
                <td>${row.water_level ? row.water_level.toFixed(1) : 'N/A'}</td>
                <td>${row.water_temperatures?.depth1 ? row.water_temperatures.depth1.toFixed(1) : 'N/A'}</td>
                <td>${row.water_temperatures?.depth2 ? row.water_temperatures.depth2.toFixed(1) : 'N/A'}</td>
                <td>${row.water_temperatures?.depth3 ? row.water_temperatures.depth3.toFixed(1) : 'N/A'}</td>
                <td><span class="status-${row.battery?.status || 'unknown'}">${row.battery?.percentage || 'N/A'}</span></td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    async function loadBatteryChart() {
        try {
            const response = await fetch('/api/admin-data.php?limit=50');
            const result = await response.json();
            
            if (result.success) {
                const data = Array.isArray(result.data) ? result.data : [result.data];
                batteryChart = window.blimasCharts.createBatteryChart('batteryChart', data);
            }
        } catch (error) {
            console.error('Error loading battery chart:', error);
        }
    }
    
    function showAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        document.querySelector('.admin-content').insertBefore(alert, document.querySelector('.admin-content').firstChild);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    // Global functions for buttons
    window.refreshData = function() {
        showAlert('Refreshing data...', 'success');
        loadDashboardData();
    };
    
    window.exportData = function() {
        // Implement data export functionality
        window.open('/admin/export-data.php', '_blank');
    };
    
    window.testAlert = function() {
        showAlert('Test alert sent successfully!', 'warning');
    };
    
    // Load initial data
    loadDashboardData();
    
    // Refresh every 30 seconds
    setInterval(loadDashboardData, 30000);
});
</script>

</div> <!-- .admin-content -->
</div> <!-- .admin-wrapper -->

<!-- JavaScript -->
<script src="../js/jquery-1.11.1.min.js"></script>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/charts.js"></script>
<script src="../assets/js/admin.js"></script>

</body>
</html>