// BLIMAS Main JavaScript
class BLIMAS {
    constructor() {
        this.init();
    }

    init() {
        this.loadSensorData();
        this.loadWeatherData();
        this.startRealTimeUpdates();
        this.initializeAnimations();
    }

    async loadSensorData() {
        try {
            const response = await fetch('/api/get_sensor_data.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                this.updateSensorDisplay(data.data);
            } else {
                console.error('Error loading sensor data:', data.message);
            }
        } catch (error) {
            console.error('Error fetching sensor data:', error);
        }
    }

    async loadWeatherData() {
        try {
            const response = await fetch('/api/get_weather.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                this.updateWeatherDisplay(data.data);
            } else {
                console.error('Error loading weather data:', data.message);
            }
        } catch (error) {
            console.error('Error fetching weather data:', error);
        }
    }

    updateSensorDisplay(data) {
        // Update temperature
        const tempElement = document.getElementById('air-temperature');
        if (tempElement) {
            this.animateValue(tempElement, parseFloat(data.air_temperature));
        }

        // Update humidity
        const humidityElement = document.getElementById('humidity');
        if (humidityElement) {
            this.animateValue(humidityElement, parseFloat(data.humidity));
        }

        // Update water level
        const waterLevelElement = document.getElementById('water-level');
        if (waterLevelElement) {
            this.animateValue(waterLevelElement, parseFloat(data.water_level));
        }

        // Update water temperatures
        const waterTemp1 = document.getElementById('water-temp-1');
        const waterTemp2 = document.getElementById('water-temp-2');
        const waterTemp3 = document.getElementById('water-temp-3');
        
        if (waterTemp1) this.animateValue(waterTemp1, parseFloat(data.water_temp_depth1));
        if (waterTemp2) this.animateValue(waterTemp2, parseFloat(data.water_temp_depth2));
        if (waterTemp3) this.animateValue(waterTemp3, parseFloat(data.water_temp_depth3));

        // Update status indicators
        this.updateStatusIndicators(data);
    }

    updateWeatherDisplay(data) {
        const weatherIcon = document.getElementById('weather-icon');
        const weatherTemp = document.getElementById('weather-temp');
        const weatherDesc = document.getElementById('weather-desc');
        const weatherHumidity = document.getElementById('weather-humidity');
        const weatherPressure = document.getElementById('weather-pressure');

        if (weatherIcon) {
            weatherIcon.innerHTML = this.getWeatherIcon(data.icon);
        }
        if (weatherTemp) {
            this.animateValue(weatherTemp, Math.round(data.temperature));
        }
        if (weatherDesc) {
            weatherDesc.textContent = data.description;
        }
        if (weatherHumidity) {
            weatherHumidity.textContent = data.humidity + '%';
        }
        if (weatherPressure) {
            weatherPressure.textContent = data.pressure + ' hPa';
        }
    }

    getWeatherIcon(iconCode) {
        const iconMap = {
            '01d': '☀️', '01n': '🌙',
            '02d': '⛅', '02n': '☁️',
            '03d': '☁️', '03n': '☁️',
            '04d': '☁️', '04n': '☁️',
            '09d': '🌧️', '09n': '🌧️',
            '10d': '🌦️', '10n': '🌧️',
            '11d': '⛈️', '11n': '⛈️',
            '13d': '❄️', '13n': '❄️',
            '50d': '🌫️', '50n': '🌫️'
        };
        return iconMap[iconCode] || '🌤️';
    }

    animateValue(element, targetValue) {
        const startValue = parseFloat(element.textContent) || 0;
        const duration = 1000;
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = startValue + (targetValue - startValue) * this.easeOutCubic(progress);
            element.textContent = currentValue.toFixed(1);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    updateStatusIndicators(data) {
        // Temperature status
        const tempStatus = this.getTemperatureStatus(data.air_temperature);
        this.updateStatusDot('temp-status', tempStatus);

        // Humidity status
        const humidityStatus = this.getHumidityStatus(data.humidity);
        this.updateStatusDot('humidity-status', humidityStatus);

        // Water level status
        const waterLevelStatus = this.getWaterLevelStatus(data.water_level);
        this.updateStatusDot('water-level-status', waterLevelStatus);
    }

    getTemperatureStatus(temp) {
        if (temp < 20 || temp > 35) return 'critical';
        if (temp < 25 || temp > 32) return 'warning';
        return 'normal';
    }

    getHumidityStatus(humidity) {
        if (humidity < 30 || humidity > 90) return 'critical';
        if (humidity < 40 || humidity > 80) return 'warning';
        return 'normal';
    }

    getWaterLevelStatus(level) {
        if (level < 1.5 || level > 3.5) return 'critical';
        if (level < 2.0 || level > 3.0) return 'warning';
        return 'normal';
    }

    updateStatusDot(elementId, status) {
        const element = document.getElementById(elementId);
        if (element) {
            element.className = `status-dot status-${status}`;
        }
    }

    startRealTimeUpdates() {
        // Update every 30 seconds
        setInterval(() => {
            this.loadSensorData();
        }, 30000);

        // Update weather every 10 minutes
        setInterval(() => {
            this.loadWeatherData();
        }, 600000);
    }

    initializeAnimations() {
        // Add entrance animations to cards
        const cards = document.querySelectorAll('.data-card, .weather-widget');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Add hover effects
        this.addHoverEffects();
    }

    addHoverEffects() {
        const cards = document.querySelectorAll('.data-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });
    }
}

// Chart utilities
class ChartManager {
    constructor() {
        this.charts = {};
    }

    async loadHistoricalData(type, hours = 24) {
        try {
            const response = await fetch(`/api/get_historical_data.php?type=${type}&hours=${hours}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                return data.data;
            } else {
                console.error('Error loading historical data:', data.message);
                return [];
            }
        } catch (error) {
            console.error('Error fetching historical data:', error);
            return [];
        }
    }

    createLineChart(canvasId, data, label, color) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        // Destroy existing chart if it exists
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        const chartData = {
            labels: data.map(item => new Date(item.timestamp).toLocaleTimeString()),
            datasets: [{
                label: label,
                data: data.map(item => item.value),
                borderColor: color,
                backgroundColor: color + '20',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
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
                elements: {
                    point: {
                        hoverBackgroundColor: color
                    }
                }
            }
        });
    }

    createWaterTemperatureChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        const chartData = {
            labels: data.map(item => new Date(item.timestamp).toLocaleTimeString()),
            datasets: [
                {
                    label: 'Depth 1 (Surface)',
                    data: data.map(item => item.water_temp_depth1),
                    borderColor: '#FF6384',
                    backgroundColor: '#FF638420',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Depth 2 (Middle)',
                    data: data.map(item => item.water_temp_depth2),
                    borderColor: '#36A2EB',
                    backgroundColor: '#36A2EB20',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Depth 3 (Bottom)',
                    data: data.map(item => item.water_temp_depth3),
                    borderColor: '#4BC0C0',
                    backgroundColor: '#4BC0C020',
                    fill: false,
                    tension: 0.4
                }
            ]
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
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
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.blimas = new BLIMAS();
    window.chartManager = new ChartManager();
});

// Utility functions
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
    }
}

function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = 'block';
    }
}

function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString();
}