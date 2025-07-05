// BLIMAS Main JavaScript
const API_BASE = './api/';

// Chart management
class ChartManager {
    constructor() {
        this.charts = {};
        this.updateInterval = null;
    }

    async loadSensorData() {
        try {
            const response = await fetch(`${API_BASE}get_sensor_data.php`);
            const result = await response.json();
            return result.status === 'success' ? result.data : null;
        } catch (error) {
            console.error('Error loading sensor data:', error);
            return null;
        }
    }

    async loadHistoricalData(type, hours) {
        try {
            const response = await fetch(`${API_BASE}get_historical_data.php?type=${type}&hours=${hours}`);
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        } catch (error) {
            console.error('Error loading historical data:', error);
            return [];
        }
    }

    async loadWeatherData() {
        try {
            const response = await fetch(`${API_BASE}get_weather.php`);
            const result = await response.json();
            return result.status === 'success' ? result.data : null;
        } catch (error) {
            console.error('Error loading weather data:', error);
            return null;
        }
    }

    async loadBatteryData() {
        try {
            const response = await fetch(`${API_BASE}get_battery_data.php`);
            const result = await response.json();
            return result.status === 'success' ? result.data : null;
        } catch (error) {
            console.error('Error loading battery data:', error);
            return null;
        }
    }

    async updateBatteryDisplay() {
        const data = await this.loadBatteryData();
        if (!data) return;

        this.updateElement('battery-level', data.battery_level, 1);
        this.updateElement('battery-voltage', data.voltage, 1);
        
        // Update last update time
        const lastUpdateElement = document.getElementById('last-update');
        if (lastUpdateElement) {
            const date = new Date(data.timestamp);
            lastUpdateElement.textContent = date.toLocaleTimeString();
        }

        // Update status indicators
        this.updateStatus('battery-status', data.battery_level, 70, 100);
        this.updateStatus('voltage-status', data.voltage, 11.5, 13.0);
    }
        if (!data) return;

        // Update sensor values
        this.updateElement('air-temperature', data.air_temperature, 1);
        this.updateElement('humidity', data.humidity, 1);
        this.updateElement('water-level', data.water_level, 2);
        this.updateElement('water-temp-1', data.water_temp_depth1, 1);
        this.updateElement('water-temp-2', data.water_temp_depth2, 1);
        this.updateElement('water-temp-3', data.water_temp_depth3, 1);

        // Update status indicators
        this.updateStatus('temp-status', data.air_temperature, 20, 35);
        this.updateStatus('humidity-status', data.humidity, 40, 80);
        this.updateStatus('water-level-status', data.water_level, 1.5, 3.0);

        // Update current values on chart pages
        this.updateElement('current-temp', data.air_temperature, 1);
        this.updateElement('current-humidity', data.humidity, 1);
        this.updateElement('current-water-level', data.water_level, 2);
    }

    updateElement(id, value, decimals = 1) {
        const element = document.getElementById(id);
        if (element && value !== null && value !== undefined) {
            element.textContent = parseFloat(value).toFixed(decimals);
        }
    }

    updateStatus(id, value, minOptimal, maxOptimal) {
        const element = document.getElementById(id);
        if (!element || value === null || value === undefined) return;

        element.className = 'status-dot';
        if (value >= minOptimal && value <= maxOptimal) {
            element.classList.add('status-normal');
        } else if (value < minOptimal * 0.8 || value > maxOptimal * 1.2) {
            element.classList.add('status-critical');
        } else {
            element.classList.add('status-warning');
        }
    }

    async updateWeatherDisplay() {
        const weather = await this.loadWeatherData();
        if (!weather) return;

        this.updateElement('weather-temp', weather.temperature, 0);
        this.updateElement('weather-humidity', weather.humidity, 0);
        this.updateElement('weather-pressure', weather.pressure, 0);
        
        const descElement = document.getElementById('weather-desc');
        if (descElement) descElement.textContent = weather.description;

        const iconElement = document.getElementById('weather-icon');
        if (iconElement) {
            const iconMap = {
                '01d': '☀️', '01n': '🌙', '02d': '⛅', '02n': '☁️',
                '03d': '☁️', '03n': '☁️', '04d': '☁️', '04n': '☁️',
                '09d': '🌦️', '09n': '🌦️', '10d': '🌧️', '10n': '🌧️',
                '11d': '⛈️', '11n': '⛈️', '13d': '❄️', '13n': '❄️',
                '50d': '🌫️', '50n': '🌫️'
            };
            iconElement.textContent = iconMap[weather.icon] || '🌤️';
        }
    }

    createLineChart(canvasId, data, label, color) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        // Destroy existing chart if it exists
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        const ctx = canvas.getContext('2d');
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => new Date(d.timestamp).toLocaleTimeString()),
                datasets: [{
                    label: label,
                    data: data.map(d => d.value),
                    borderColor: color,
                    backgroundColor: color + '20',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    createMultiLineChart(canvasId, data, labels, colors) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        const ctx = canvas.getContext('2d');
        const datasets = labels.map((label, index) => ({
            label: label,
            data: data.map(d => d[`water_temp_depth${index + 1}`]),
            borderColor: colors[index],
            backgroundColor: colors[index] + '20',
            borderWidth: 2,
            fill: false,
            tension: 0.4
        }));

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => new Date(d.timestamp).toLocaleTimeString()),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                }
            }
        });
    }

    createWaterTemperatureChart(canvasId, data) {
        const labels = ['Surface (Depth 1)', 'Middle (Depth 2)', 'Bottom (Depth 3)'];
        const colors = ['#ff6b6b', '#feca57', '#48dbfb'];
        this.createMultiLineChart(canvasId, data, labels, colors);
    }

    startRealTimeUpdates() {
        this.updateInterval = setInterval(async () => {
            const data = await this.loadSensorData();
            this.updateSensorDisplay(data);
            await this.updateWeatherDisplay();
            await this.updateBatteryDisplay();
        }, 30000); // Update every 30 seconds
    }

    stopRealTimeUpdates() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }
        this.updateInterval = setInterval(async () => {
            const data = await this.loadSensorData();
            this.updateSensorDisplay(data);
            this.updateWeatherDisplay();
        }, 30000); // Update every 30 seconds
    }

    stopRealTimeUpdates() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }
}

// Global chart manager instance
window.chartManager = new ChartManager();

// Chart loading functions for individual pages
async function loadTemperatureChart(button, hours) {
    setActiveButton(button);
    const data = await window.chartManager.loadHistoricalData('temperature', hours);
    if (data.length > 0) {
        window.chartManager.createLineChart('temperature-chart', data, 'Air Temperature (°C)', '#667eea');
        updateStatsFromData(data, 'temp');
    }
}

async function loadHumidityChart(button, hours) {
    setActiveButton(button);
    const data = await window.chartManager.loadHistoricalData('humidity', hours);
    if (data.length > 0) {
        window.chartManager.createLineChart('humidity-chart', data, 'Humidity (%)', '#4ecdc4');
        updateStatsFromData(data, 'humidity');
    }
}

async function loadWaterLevelChart(button, hours) {
    setActiveButton(button);
    const data = await window.chartManager.loadHistoricalData('water_level', hours);
    if (data.length > 0) {
        window.chartManager.createLineChart('water-level-chart', data, 'Water Level (m)', '#45b7d1');
        updateStatsFromData(data, 'water-level');
    }
}

async function loadWaterTempChart(button, hours) {
    setActiveButton(button);
    const data = await window.chartManager.loadHistoricalData('water_temperature', hours);
    if (data.length > 0) {
        const labels = ['Surface (Depth 1)', 'Middle (Depth 2)', 'Bottom (Depth 3)'];
        const colors = ['#ff6b6b', '#feca57', '#48dbfb'];
        window.chartManager.createMultiLineChart('water-temp-chart', data, labels, colors);
    }
}

function setActiveButton(activeButton) {
    document.querySelectorAll('.time-btn').forEach(btn => btn.classList.remove('active'));
    activeButton.classList.add('active');
}

function updateStatsFromData(data, prefix) {
    if (data.length === 0) return;
    
    const values = data.map(d => parseFloat(d.value));
    const current = values[values.length - 1];
    const max = Math.max(...values);
    const min = Math.min(...values);
    
    window.chartManager.updateElement(`current-${prefix}`, current, prefix === 'water-level' ? 2 : 1);
    window.chartManager.updateElement(`max-${prefix}`, max, prefix === 'water-level' ? 2 : 1);
    window.chartManager.updateElement(`min-${prefix}`, min, prefix === 'water-level' ? 2 : 1);
}

// Mobile navigation
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const hamburger = document.querySelector('.hamburger');
    
    navMenu.classList.toggle('active');
    hamburger.classList.toggle('active');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', async () => {
    // Load initial data
    const sensorData = await window.chartManager.loadSensorData();
    window.chartManager.updateSensorDisplay(sensorData);
    
    // Load weather data
    await window.chartManager.updateWeatherDisplay();
    
    // Load battery data
    await window.chartManager.updateBatteryDisplay();
    
    // Start real-time updates
    window.chartManager.startRealTimeUpdates();
    
    // Add mobile menu event listener
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', toggleMobileMenu);
    }
});