// BLIMAS Main JavaScript
class BLIMAS {
    constructor() {
        this.init();
    }

    init() {
        this.loadSensorData();
        this.loadWeatherData();
        this.initializeAIAnalysis();
        this.startRealTimeUpdates();
        this.initializeAnimations();
        this.initializeTabs();
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

        // Update AI analysis every 5 minutes
        setInterval(() => {
            this.initializeAIAnalysis();
        }, 300000);
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

    // AI Analysis Methods
    initializeAIAnalysis() {
        this.loadTrendAnalysis();
        this.loadPredictions();
        this.loadAnomalies();
        this.loadSummary();
    }

    initializeTabs() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to clicked button
                button.classList.add('active');

                // Show corresponding content
                const tabId = button.dataset.tab + '-tab';
                const targetContent = document.getElementById(tabId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }

    async loadTrendAnalysis() {
        try {
            const response = await fetch('/api/get_analysis.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                this.displayTrendAnalysis(data.data);
            } else {
                console.error('Error loading trend analysis:', data.message);
                this.showAnalysisError('trends');
            }
        } catch (error) {
            console.error('Error fetching trend analysis:', error);
            this.showAnalysisError('trends');
        }
    }

    async loadPredictions() {
        try {
            const response = await fetch('/api/get_predictions.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                this.displayPredictions(data.data);
            } else {
                console.error('Error loading predictions:', data.message);
                this.showAnalysisError('predictions');
            }
        } catch (error) {
            console.error('Error fetching predictions:', error);
            this.showAnalysisError('predictions');
        }
    }

    async loadAnomalies() {
        try {
            const response = await fetch('/api/get_anomalies.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                this.displayAnomalies(data.data);
            } else {
                console.error('Error loading anomalies:', data.message);
                this.showAnalysisError('anomalies');
            }
        } catch (error) {
            console.error('Error fetching anomalies:', error);
            this.showAnalysisError('anomalies');
        }
    }

    async loadSummary() {
        try {
            const response = await fetch('/api/get_summary.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                this.displaySummary(data.data);
            } else {
                console.error('Error loading summary:', data.message);
                this.showAnalysisError('summary');
            }
        } catch (error) {
            console.error('Error fetching summary:', error);
            this.showAnalysisError('summary');
        }
    }

    displayTrendAnalysis(data) {
        // Display AI insights
        const tempInsight = document.getElementById('temperature-trend-insight');
        const humidityInsight = document.getElementById('humidity-trend-insight');
        const waterInsight = document.getElementById('water-trend-insight');

        if (data.ai_analysis && data.ai_analysis.trends) {
            const trends = data.ai_analysis.trends;
            
            if (tempInsight) {
                tempInsight.innerHTML = `
                    <h4>🤖 AI Analysis</h4>
                    <p><strong>Air Temperature:</strong> ${trends.air_temperature || 'Analysis in progress...'}</p>
                `;
            }
            
            if (humidityInsight) {
                humidityInsight.innerHTML = `
                    <h4>🤖 AI Analysis</h4>
                    <p><strong>Humidity:</strong> ${trends.humidity || 'Analysis in progress...'}</p>
                `;
            }
            
            if (waterInsight) {
                waterInsight.innerHTML = `
                    <h4>🤖 AI Analysis</h4>
                    <p><strong>Water Level:</strong> ${trends.water_level || 'Analysis in progress...'}</p>
                    <p><strong>Health Status:</strong> <span class="status-${data.ai_analysis.health_status}">${data.ai_analysis.health_status}</span></p>
                `;
            }
        }

        // Create trend charts with statistical data
        if (data.statistics) {
            this.createTrendCharts(data.statistics);
        }
    }

    displayPredictions(data) {
        const prediction24h = document.getElementById('prediction-24h');
        const predictionInsight = document.getElementById('prediction-insight');

        if (data.statistical_predictions && prediction24h) {
            let predictionsHTML = '';
            
            for (const [metric, pred] of Object.entries(data.statistical_predictions)) {
                const metricName = this.formatMetricName(metric);
                predictionsHTML += `
                    <div class="prediction-item">
                        <div class="prediction-label">${metricName}</div>
                        <div>
                            <span class="prediction-value">${pred.next_24h}</span>
                            <span class="confidence-badge ${pred.confidence}">${pred.confidence}</span>
                            <div style="font-size: 12px; color: #6b7280;">${pred.trend_direction}</div>
                        </div>
                    </div>
                `;
            }
            
            prediction24h.innerHTML = predictionsHTML;
        }

        if (data.ai_predictions && predictionInsight) {
            let insightHTML = '<h4>🤖 AI Predictions</h4>';
            
            if (data.ai_predictions.predictions) {
                const preds = data.ai_predictions.predictions;
                insightHTML += `
                    <p><strong>Temperature:</strong> ${preds.air_temperature?.next_24h || 'Analyzing...'}</p>
                    <p><strong>Humidity:</strong> ${preds.humidity?.next_24h || 'Analyzing...'}</p>
                    <p><strong>Water Level:</strong> ${preds.water_level?.next_24h || 'Analyzing...'}</p>
                `;
            }
            
            predictionInsight.innerHTML = insightHTML;
        }

        // Create prediction chart
        this.createPredictionChart(data.statistical_predictions);
    }

    displayAnomalies(data) {
        const anomalyAlerts = document.getElementById('anomaly-alerts');
        const anomalyInsight = document.getElementById('anomaly-insight');

        if (data.statistical_anomalies && anomalyAlerts) {
            if (data.statistical_anomalies.length === 0) {
                anomalyAlerts.innerHTML = `
                    <div class="anomaly-alert low">
                        <div class="anomaly-metric">✅ No anomalies detected</div>
                        <div>All sensor readings are within normal parameters</div>
                    </div>
                `;
            } else {
                let alertsHTML = '';
                
                data.statistical_anomalies.forEach(anomaly => {
                    alertsHTML += `
                        <div class="anomaly-alert ${anomaly.severity}">
                            <div class="anomaly-metric">${this.formatMetricName(anomaly.metric)}</div>
                            <div class="anomaly-value">Value: ${anomaly.value}</div>
                            <div class="anomaly-time">${new Date(anomaly.timestamp).toLocaleString()}</div>
                        </div>
                    `;
                });
                
                anomalyAlerts.innerHTML = alertsHTML;
            }
        }

        if (data.ai_analysis && anomalyInsight) {
            let insightHTML = '<h4>🤖 AI Anomaly Analysis</h4>';
            
            if (data.ai_analysis.anomalies) {
                insightHTML += `<p>${data.ai_analysis.anomalies.detected || 'No significant anomalies detected'}</p>`;
            }
            
            if (data.ai_analysis.risk_assessment) {
                insightHTML += `<p><strong>Risk Level:</strong> <span class="status-${data.ai_analysis.risk_assessment}">${data.ai_analysis.risk_assessment.replace('_', ' ')}</span></p>`;
            }
            
            anomalyInsight.innerHTML = insightHTML;
        }
    }

    displaySummary(data) {
        const executiveSummary = document.getElementById('executive-summary');
        const keyMetrics = document.getElementById('key-metrics');
        const summaryRecommendations = document.getElementById('summary-recommendations');

        if (data.summary_statistics && executiveSummary) {
            const stats = data.summary_statistics;
            let summaryHTML = `
                <div class="metric-item">
                    <span class="metric-label">Data Quality:</span>
                    <span class="metric-value">${stats.data_quality?.completeness || 'N/A'}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Environmental Status:</span>
                    <span class="status-${stats.environmental_status?.status}">${stats.environmental_status?.status || 'Unknown'}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Last Updated:</span>
                    <span class="metric-value">${new Date(stats.latest_reading?.timestamp).toLocaleString()}</span>
                </div>
            `;
            
            executiveSummary.innerHTML = summaryHTML;
        }

        if (data.summary_statistics?.latest_reading && keyMetrics) {
            const latest = data.summary_statistics.latest_reading;
            let metricsHTML = `
                <div class="metric-item">
                    <span class="metric-label">Air Temperature:</span>
                    <span class="metric-value">${latest.air_temperature}°C</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Humidity:</span>
                    <span class="metric-value">${latest.humidity}%</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Water Level:</span>
                    <span class="metric-value">${latest.water_level}m</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Surface Water Temp:</span>
                    <span class="metric-value">${latest.water_temp_depth1}°C</span>
                </div>
            `;
            
            keyMetrics.innerHTML = metricsHTML;
        }

        if (data.ai_summary && summaryRecommendations) {
            let recHTML = '<h4>🤖 AI Recommendations</h4>';
            
            if (data.ai_summary.recommendations) {
                data.ai_summary.recommendations.forEach(rec => {
                    recHTML += `<p>• ${rec}</p>`;
                });
            }
            
            summaryRecommendations.innerHTML = recHTML;
        }
    }

    createTrendCharts(statistics) {
        // Temperature trend chart
        if (statistics.air_temperature) {
            const ctx = document.getElementById('temperature-trend-chart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Min', 'Avg', 'Max'],
                        datasets: [{
                            label: 'Temperature (°C)',
                            data: [statistics.air_temperature.min, statistics.air_temperature.avg, statistics.air_temperature.max],
                            borderColor: '#ff6b6b',
                            backgroundColor: 'rgba(255, 107, 107, 0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        }

        // Humidity trend chart
        if (statistics.humidity) {
            const ctx = document.getElementById('humidity-trend-chart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Min', 'Avg', 'Max'],
                        datasets: [{
                            label: 'Humidity (%)',
                            data: [statistics.humidity.min, statistics.humidity.avg, statistics.humidity.max],
                            borderColor: '#4ecdc4',
                            backgroundColor: 'rgba(78, 205, 196, 0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        }

        // Water analysis chart
        const ctx = document.getElementById('water-analysis-chart');
        if (ctx && statistics.water_temp_depth1 && statistics.water_temp_depth2 && statistics.water_temp_depth3) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Surface', 'Middle', 'Bottom'],
                    datasets: [{
                        label: 'Water Temperature (°C)',
                        data: [
                            statistics.water_temp_depth1.avg,
                            statistics.water_temp_depth2.avg,
                            statistics.water_temp_depth3.avg
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(75, 192, 192, 0.6)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    }

    createPredictionChart(predictions) {
        const ctx = document.getElementById('prediction-chart');
        if (!ctx || !predictions) return;

        const metrics = Object.keys(predictions);
        const next1h = metrics.map(m => predictions[m].next_1h);
        const next24h = metrics.map(m => predictions[m].next_24h);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: metrics.map(m => this.formatMetricName(m)),
                datasets: [
                    {
                        label: 'Next 1 Hour',
                        data: next1h,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: false
                    },
                    {
                        label: 'Next 24 Hours',
                        data: next24h,
                        borderColor: '#764ba2',
                        backgroundColor: 'rgba(118, 75, 162, 0.1)',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    }

    formatMetricName(metric) {
        const names = {
            'air_temperature': 'Air Temperature',
            'humidity': 'Humidity',
            'water_level': 'Water Level',
            'water_temp_depth1': 'Surface Water Temp',
            'water_temp_depth2': 'Middle Water Temp',
            'water_temp_depth3': 'Bottom Water Temp'
        };
        return names[metric] || metric;
    }

    showAnalysisError(type) {
        const elements = document.querySelectorAll(`#${type}-tab .loading-spinner`);
        elements.forEach(el => {
            el.innerHTML = '⚠️ Unable to load analysis data. Please try again later.';
            el.style.color = '#ef4444';
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