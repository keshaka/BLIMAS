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
            console.log('Loading sensor data...');
            const response = await fetch('api/get_sensor_data.php');
            const data = await response.json();
            
            console.log('Sensor data response:', data);
            
            if (data.status === 'success') {
                this.updateSensorDisplay(data.data);
            } else {
                console.error('Error loading sensor data:', data.message);
                this.showErrorMessage('Failed to load sensor data: ' + data.message);
            }
        } catch (error) {
            console.error('Error fetching sensor data:', error);
            this.showErrorMessage('Network error loading sensor data');
        }
    }

    async loadWeatherData() {
        try {
            console.log('Loading weather data...');
            const response = await fetch('api/get_weather.php');
            const data = await response.json();
            
            console.log('Weather data response:', data);
            
            if (data.status === 'success') {
                this.updateWeatherDisplay(data.data);
            } else {
                console.error('Error loading weather data:', data.message);
                // Don't show error for weather as it might not be configured
            }
        } catch (error) {
            console.error('Error fetching weather data:', error);
            // Don't show error for weather as it might not be configured
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

    showErrorMessage(message) {
        // Create or update error display
        let errorDiv = document.getElementById('error-display');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'error-display';
            errorDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #e74c3c;
                color: white;
                padding: 15px;
                border-radius: 5px;
                z-index: 1000;
                max-width: 300px;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            document.body.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
        errorDiv.style.opacity = '1';
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            errorDiv.style.opacity = '0';
        }, 5000);
    }
}

// Chart utilities
class ChartManager {
    constructor() {
        this.charts = {};
    }

    async loadHistoricalData(type, hours = 24) {
        try {
            console.log(`Loading historical data: ${type}, ${hours} hours`);
            const response = await fetch(`api/get_historical_data.php?type=${type}&hours=${hours}`);
            const data = await response.json();
            
            console.log(`Historical data response for ${type}:`, data);
            
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
        if (!ctx) {
            console.error(`Canvas element not found: ${canvasId}`);
            return;
        }

        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.log('Chart.js not available, using fallback chart implementation');
            this.createFallbackChart(canvasId, data, label, color);
            return;
        }

        // Destroy existing chart if it exists
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        console.log(`Creating chart for ${canvasId} with ${data.length} data points`);

        const chartData = {
            labels: data.map(item => new Date(item.timestamp)),
            datasets: [{
                label: label,
                data: data.map(item => ({
                    x: new Date(item.timestamp),
                    y: parseFloat(item.value)
                })),
                borderColor: color,
                backgroundColor: color + '20',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        };

        try {
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
                            type: 'time',
                            time: {
                                displayFormats: {
                                    hour: 'HH:mm',
                                    day: 'MMM dd'
                                }
                            },
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
        } catch (error) {
            console.error('Error creating chart:', error);
            this.createFallbackChart(canvasId, data, label, color);
        }
    }

    createFallbackChart(canvasId, data, label, color) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = canvas.offsetHeight;
        const padding = 60;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        if (!data || data.length === 0) {
            ctx.fillStyle = '#666';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No data available', width / 2, height / 2);
            return;
        }

        const values = data.map(item => parseFloat(item.value));
        const timestamps = data.map(item => new Date(item.timestamp));
        const minValue = Math.min(...values);
        const maxValue = Math.max(...values);
        const valueRange = maxValue - minValue || 1;

        // Draw background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);

        // Draw grid
        ctx.strokeStyle = '#e9ecef';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 10; i++) {
            const x = padding + (i * (width - 2 * padding) / 10);
            ctx.beginPath();
            ctx.moveTo(x, padding);
            ctx.lineTo(x, height - padding);
            ctx.stroke();
        }
        for (let i = 0; i <= 5; i++) {
            const y = padding + (i * (height - 2 * padding) / 5);
            ctx.beginPath();
            ctx.moveTo(padding, y);
            ctx.lineTo(width - padding, y);
            ctx.stroke();
        }

        // Draw axes
        ctx.strokeStyle = '#495057';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(padding, padding);
        ctx.lineTo(padding, height - padding);
        ctx.lineTo(width - padding, height - padding);
        ctx.stroke();

        // Draw data line
        if (values.length >= 2) {
            ctx.strokeStyle = color;
            ctx.lineWidth = 3;
            ctx.beginPath();
            for (let i = 0; i < values.length; i++) {
                const x = padding + (i * (width - 2 * padding) / (values.length - 1));
                const y = height - padding - ((values[i] - minValue) / valueRange * (height - 2 * padding));
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }
            ctx.stroke();

            // Draw points
            ctx.fillStyle = color;
            for (let i = 0; i < values.length; i++) {
                const x = padding + (i * (width - 2 * padding) / (values.length - 1));
                const y = height - padding - ((values[i] - minValue) / valueRange * (height - 2 * padding));
                ctx.beginPath();
                ctx.arc(x, y, 4, 0, 2 * Math.PI);
                ctx.fill();
            }
        }

        // Draw labels
        ctx.fillStyle = '#495057';
        ctx.font = '12px Arial';
        
        // Y axis labels
        for (let i = 0; i <= 5; i++) {
            const value = minValue + (i * valueRange / 5);
            const y = height - padding - (i * (height - 2 * padding) / 5);
            ctx.textAlign = 'right';
            ctx.fillText(value.toFixed(1), padding - 10, y + 4);
        }

        // X axis labels
        const labelCount = Math.min(5, timestamps.length);
        for (let i = 0; i < labelCount; i++) {
            const index = Math.floor(i * (timestamps.length - 1) / (labelCount - 1));
            const x = padding + (index * (width - 2 * padding) / (timestamps.length - 1));
            const timeStr = timestamps[index].toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            ctx.textAlign = 'center';
            ctx.fillText(timeStr, x, height - padding + 20);
        }

        // Chart title
        ctx.font = 'bold 16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(label, width / 2, 30);

        // Store reference for cleanup
        this.charts[canvasId] = {
            destroy: () => ctx.clearRect(0, 0, width, height)
        };
    }

    createWaterTemperatureChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.error(`Canvas element not found: ${canvasId}`);
            return;
        }

        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.log('Chart.js not available, using fallback multi-line chart implementation');
            this.createFallbackMultiLineChart(canvasId, data);
            return;
        }

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        console.log(`Creating water temperature chart for ${canvasId} with ${data.length} data points`);

        const chartData = {
            labels: data.map(item => new Date(item.timestamp)),
            datasets: [
                {
                    label: 'Depth 1 (Surface)',
                    data: data.map(item => ({
                        x: new Date(item.timestamp),
                        y: parseFloat(item.water_temp_depth1)
                    })),
                    borderColor: '#FF6384',
                    backgroundColor: '#FF638420',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Depth 2 (Middle)',
                    data: data.map(item => ({
                        x: new Date(item.timestamp),
                        y: parseFloat(item.water_temp_depth2)
                    })),
                    borderColor: '#36A2EB',
                    backgroundColor: '#36A2EB20',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Depth 3 (Bottom)',
                    data: data.map(item => ({
                        x: new Date(item.timestamp),
                        y: parseFloat(item.water_temp_depth3)
                    })),
                    borderColor: '#4BC0C0',
                    backgroundColor: '#4BC0C020',
                    fill: false,
                    tension: 0.4
                }
            ]
        };

        try {
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
                            type: 'time',
                            time: {
                                displayFormats: {
                                    hour: 'HH:mm',
                                    day: 'MMM dd'
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating water temperature chart:', error);
            this.createFallbackMultiLineChart(canvasId, data);
        }
    }

    createFallbackMultiLineChart(canvasId, data) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = canvas.offsetHeight;
        const padding = 80;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        if (!data || data.length === 0) {
            ctx.fillStyle = '#666';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No data available', width / 2, height / 2);
            return;
        }

        const timestamps = data.map(item => new Date(item.timestamp));
        const depth1Values = data.map(item => parseFloat(item.water_temp_depth1));
        const depth2Values = data.map(item => parseFloat(item.water_temp_depth2));
        const depth3Values = data.map(item => parseFloat(item.water_temp_depth3));
        
        const allValues = [...depth1Values, ...depth2Values, ...depth3Values];
        const minValue = Math.min(...allValues);
        const maxValue = Math.max(...allValues);
        const valueRange = maxValue - minValue || 1;

        // Draw background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);

        // Draw grid
        ctx.strokeStyle = '#e9ecef';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 10; i++) {
            const x = padding + (i * (width - 2 * padding) / 10);
            ctx.beginPath();
            ctx.moveTo(x, padding);
            ctx.lineTo(x, height - padding);
            ctx.stroke();
        }
        for (let i = 0; i <= 5; i++) {
            const y = padding + (i * (height - 2 * padding) / 5);
            ctx.beginPath();
            ctx.moveTo(padding, y);
            ctx.lineTo(width - padding, y);
            ctx.stroke();
        }

        // Draw axes
        ctx.strokeStyle = '#495057';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(padding, padding);
        ctx.lineTo(padding, height - padding);
        ctx.lineTo(width - padding, height - padding);
        ctx.stroke();

        // Draw three lines for the three depths
        const colors = ['#FF6384', '#36A2EB', '#4BC0C0'];
        const labels = ['Surface (Depth 1)', 'Middle (Depth 2)', 'Bottom (Depth 3)'];
        const datasets = [depth1Values, depth2Values, depth3Values];

        datasets.forEach((values, datasetIndex) => {
            if (values.length >= 2) {
                // Draw line
                ctx.strokeStyle = colors[datasetIndex];
                ctx.lineWidth = 3;
                ctx.beginPath();
                for (let i = 0; i < values.length; i++) {
                    const x = padding + (i * (width - 2 * padding) / (values.length - 1));
                    const y = height - padding - ((values[i] - minValue) / valueRange * (height - 2 * padding));
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.stroke();

                // Draw points
                ctx.fillStyle = colors[datasetIndex];
                for (let i = 0; i < values.length; i++) {
                    const x = padding + (i * (width - 2 * padding) / (values.length - 1));
                    const y = height - padding - ((values[i] - minValue) / valueRange * (height - 2 * padding));
                    ctx.beginPath();
                    ctx.arc(x, y, 3, 0, 2 * Math.PI);
                    ctx.fill();
                }
            }
        });

        // Draw labels
        ctx.fillStyle = '#495057';
        ctx.font = '12px Arial';
        
        // Y axis labels
        for (let i = 0; i <= 5; i++) {
            const value = minValue + (i * valueRange / 5);
            const y = height - padding - (i * (height - 2 * padding) / 5);
            ctx.textAlign = 'right';
            ctx.fillText(value.toFixed(1), padding - 10, y + 4);
        }

        // X axis labels
        const labelCount = Math.min(5, timestamps.length);
        for (let i = 0; i < labelCount; i++) {
            const index = Math.floor(i * (timestamps.length - 1) / (labelCount - 1));
            const x = padding + (index * (width - 2 * padding) / (timestamps.length - 1));
            const timeStr = timestamps[index].toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            ctx.textAlign = 'center';
            ctx.fillText(timeStr, x, height - padding + 20);
        }

        // Chart title
        ctx.font = 'bold 16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Water Temperature by Depth (°C)', width / 2, 30);

        // Legend
        ctx.font = '12px Arial';
        const legendY = 50;
        labels.forEach((label, index) => {
            const legendX = 20 + index * 150;
            ctx.fillStyle = colors[index];
            ctx.fillRect(legendX, legendY, 15, 3);
            ctx.fillStyle = '#495057';
            ctx.textAlign = 'left';
            ctx.fillText(label, legendX + 20, legendY + 10);
        });

        // Store reference for cleanup
        this.charts[canvasId] = {
            destroy: () => ctx.clearRect(0, 0, width, height)
        };
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

// Add CSS for error messages
const errorStyles = `
<style>
.error-message {
    text-align: center;
    padding: 40px 20px;
    color: #e74c3c;
    font-size: 14px;
    background: rgba(231, 76, 60, 0.1);
    border: 1px solid rgba(231, 76, 60, 0.3);
    border-radius: 5px;
    margin: 20px;
}
</style>
`;
document.head.insertAdjacentHTML('beforeend', errorStyles);