/**
 * Dashboard JavaScript for BLIMAS
 * Handles real-time data updates, charts, and weather integration
 */

class BLIMASDashboard {
    constructor() {
        this.refreshInterval = 5000; // 5 seconds
        this.chartRefreshInterval = 30000; // 30 seconds
        this.charts = {};
        this.lastDataTimestamp = null;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadInitialData();
        this.startDataRefresh();
        this.loadWeatherData();
        this.setupCharts();
    }
    
    setupEventListeners() {
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopDataRefresh();
            } else {
                this.startDataRefresh();
            }
        });
        
        // Handle window focus/blur
        window.addEventListener('focus', () => this.startDataRefresh());
        window.addEventListener('blur', () => this.stopDataRefresh());
    }
    
    async loadInitialData() {
        try {
            await this.fetchSensorData();
            console.log('Initial data loaded successfully');
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showAlert('Error loading sensor data', 'error');
        }
    }
    
    async fetchSensorData() {
        try {
            const response = await fetch('api/get-data.php?nocache=' + new Date().getTime());
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success && result.data) {
                this.updateSensorDisplays(result.data);
                this.lastDataTimestamp = result.data.timestamp;
                this.showDataStatus('connected');
            } else {
                throw new Error(result.message || 'No data available');
            }
        } catch (error) {
            console.error('Error fetching sensor data:', error);
            this.showDataStatus('disconnected');
            throw error;
        }
    }
    
    updateSensorDisplays(data) {
        // Update air temperature
        this.updateCard('air-temp', data.air_temp, '°C', this.getTemperatureStatus(data.air_temp));
        
        // Update humidity
        this.updateCard('humidity', data.humidity, '%', this.getHumidityStatus(data.humidity));
        
        // Update water level
        this.updateCard('water-level', data.water_level, 'cm', this.getWaterLevelStatus(data.water_level));
        
        // Update battery level
        this.updateCard('battery-level', data.battery_level, '%', this.getBatteryStatus(data.battery_level));
        
        // Update water temperatures
        if (document.getElementById('water-temp1-value')) {
            document.getElementById('water-temp1-value').textContent = data.water_temp1.toFixed(1);
        }
        if (document.getElementById('water-temp2-value')) {
            document.getElementById('water-temp2-value').textContent = data.water_temp2.toFixed(1);
        }
        if (document.getElementById('water-temp3-value')) {
            document.getElementById('water-temp3-value').textContent = data.water_temp3.toFixed(1);
        }
        
        // Update last updated time
        this.updateLastUpdated(data.timestamp);
    }
    
    updateCard(elementId, value, unit, status) {
        const valueElement = document.getElementById(elementId + '-value');
        const statusElement = document.getElementById(elementId + '-status');
        
        if (valueElement) {
            valueElement.textContent = typeof value === 'number' ? value.toFixed(1) : value;
        }
        
        if (statusElement) {
            statusElement.innerHTML = `<span class="status-indicator status-${status.type}"></span>${status.text}`;
        }
    }
    
    getTemperatureStatus(temp) {
        if (temp > 35) return { type: 'critical', text: 'Very Hot' };
        if (temp > 30) return { type: 'warning', text: 'Hot' };
        if (temp < 15) return { type: 'warning', text: 'Cold' };
        return { type: 'good', text: 'Normal' };
    }
    
    getHumidityStatus(humidity) {
        if (humidity > 90) return { type: 'warning', text: 'Very Humid' };
        if (humidity > 70) return { type: 'good', text: 'Humid' };
        if (humidity < 30) return { type: 'warning', text: 'Dry' };
        return { type: 'good', text: 'Normal' };
    }
    
    getWaterLevelStatus(level) {
        if (level > 200) return { type: 'warning', text: 'High' };
        if (level < 50) return { type: 'critical', text: 'Low' };
        return { type: 'good', text: 'Normal' };
    }
    
    getBatteryStatus(battery) {
        if (battery < 20) return { type: 'critical', text: 'Low Battery' };
        if (battery < 40) return { type: 'warning', text: 'Medium' };
        return { type: 'good', text: 'Good' };
    }
    
    async loadWeatherData() {
        try {
            const response = await fetch('api/weather.php?nocache=' + new Date().getTime());
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success && result.data) {
                this.updateWeatherDisplay(result.data);
            } else {
                throw new Error(result.message || 'No weather data available');
            }
        } catch (error) {
            console.error('Error fetching weather data:', error);
            this.showAlert('Error loading weather data', 'warning');
        }
    }
    
    updateWeatherDisplay(data) {
        // Update weather elements
        const elements = {
            'weather-location': data.location,
            'weather-temp': data.temperature + '°C',
            'weather-condition': data.condition,
            'weather-humidity-val': data.humidity + '%',
            'weather-wind-val': data.wind_speed + ' kph ' + data.wind_direction,
            'weather-pressure-val': data.pressure + ' mb',
            'weather-visibility-val': data.visibility + ' km',
            'weather-feels-like-val': data.feels_like + '°C'
        };
        
        for (const [id, value] of Object.entries(elements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        }
        
        // Update weather icon
        const iconElement = document.getElementById('weather-icon');
        if (iconElement && data.icon) {
            iconElement.src = 'https:' + data.icon;
            iconElement.alt = data.condition;
        }
    }
    
    updateLastUpdated(timestamp) {
        const element = document.getElementById('last-updated');
        if (element) {
            const date = new Date(timestamp);
            element.textContent = `Last updated: ${date.toLocaleString()}`;
        }
    }
    
    showDataStatus(status) {
        const statusElement = document.getElementById('connection-status');
        if (statusElement) {
            statusElement.className = `status-indicator status-${status === 'connected' ? 'good' : 'critical'}`;
        }
    }
    
    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alert-container') || this.createAlertContainer();
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <strong>${type.charAt(0).toUpperCase() + type.slice(1)}:</strong> ${message}
            <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
        `;
        
        alertContainer.appendChild(alert);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    }
    
    createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alert-container';
        container.className = 'alert-container';
        document.body.appendChild(container);
        return container;
    }
    
    setupCharts() {
        // Setup mini chart for dashboard if canvas exists
        const chartCanvas = document.getElementById('dashboard-chart');
        if (chartCanvas) {
            this.setupDashboardChart(chartCanvas);
        }
    }
    
    setupDashboardChart(canvas) {
        const ctx = canvas.getContext('2d');
        
        this.charts.dashboard = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Air Temperature',
                    data: [],
                    borderColor: '#ff6b6b',
                    backgroundColor: 'rgba(255, 107, 107, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Humidity',
                    data: [],
                    borderColor: '#4ecdc4',
                    backgroundColor: 'rgba(78, 205, 196, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        type: 'linear',
                        display: false,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: false,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }
            }
        });
    }
    
    startDataRefresh() {
        this.stopDataRefresh(); // Clear any existing interval
        
        this.dataInterval = setInterval(async () => {
            try {
                await this.fetchSensorData();
            } catch (error) {
                console.error('Error in data refresh:', error);
            }
        }, this.refreshInterval);
        
        // Refresh weather data less frequently
        this.weatherInterval = setInterval(async () => {
            try {
                await this.loadWeatherData();
            } catch (error) {
                console.error('Error in weather refresh:', error);
            }
        }, 300000); // 5 minutes
    }
    
    stopDataRefresh() {
        if (this.dataInterval) {
            clearInterval(this.dataInterval);
            this.dataInterval = null;
        }
        
        if (this.weatherInterval) {
            clearInterval(this.weatherInterval);
            this.weatherInterval = null;
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.blimasDashboard = new BLIMASDashboard();
});

// Legacy support for existing data.js functionality
async function fetchData() {
    if (window.blimasDashboard) {
        try {
            await window.blimasDashboard.fetchSensorData();
        } catch (error) {
            console.error('Legacy fetchData error:', error);
        }
    }
}