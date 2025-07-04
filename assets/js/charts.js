// BLIMAS Chart Management System
class BLIMASCharts {
    constructor() {
        this.charts = {};
        this.defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        displayFormats: {
                            minute: 'HH:mm',
                            hour: 'HH:mm',
                            day: 'MM/DD'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    beginAtZero: false,
                    title: {
                        display: true
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        };
        
        this.colors = {
            primary: '#3498db',
            secondary: '#2c3e50',
            success: '#27ae60',
            warning: '#f39c12',
            danger: '#e74c3c',
            info: '#17a2b8',
            light: '#f8f9fa',
            dark: '#343a40'
        };
    }
    
    // Create temperature chart
    createTemperatureChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const chartData = {
            datasets: [{
                label: 'Air Temperature',
                data: data.map(item => ({
                    x: new Date(item.timestamp),
                    y: item.air_temperature
                })),
                borderColor: this.colors.danger,
                backgroundColor: this.colors.danger + '20',
                fill: true,
                tension: 0.4
            }]
        };
        
        const options = {
            ...this.defaultOptions,
            scales: {
                ...this.defaultOptions.scales,
                y: {
                    ...this.defaultOptions.scales.y,
                    title: {
                        display: true,
                        text: 'Temperature (°C)'
                    }
                }
            }
        };
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: options
        });
        
        return this.charts[canvasId];
    }
    
    // Create humidity chart
    createHumidityChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const chartData = {
            datasets: [{
                label: 'Humidity',
                data: data.map(item => ({
                    x: new Date(item.timestamp),
                    y: item.humidity
                })),
                borderColor: this.colors.info,
                backgroundColor: this.colors.info + '20',
                fill: true,
                tension: 0.4
            }]
        };
        
        const options = {
            ...this.defaultOptions,
            scales: {
                ...this.defaultOptions.scales,
                y: {
                    ...this.defaultOptions.scales.y,
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Humidity (%)'
                    }
                }
            }
        };
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: options
        });
        
        return this.charts[canvasId];
    }
    
    // Create water level chart
    createWaterLevelChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const chartData = {
            datasets: [{
                label: 'Water Level',
                data: data.map(item => ({
                    x: new Date(item.timestamp),
                    y: item.water_level
                })),
                borderColor: this.colors.primary,
                backgroundColor: this.colors.primary + '20',
                fill: true,
                tension: 0.4
            }]
        };
        
        const options = {
            ...this.defaultOptions,
            scales: {
                ...this.defaultOptions.scales,
                y: {
                    ...this.defaultOptions.scales.y,
                    title: {
                        display: true,
                        text: 'Water Level (cm)'
                    }
                }
            }
        };
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: options
        });
        
        return this.charts[canvasId];
    }
    
    // Create water temperature chart (multiple depths)
    createWaterTemperatureChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const chartData = {
            datasets: [
                {
                    label: 'Depth 1',
                    data: data.map(item => ({
                        x: new Date(item.timestamp),
                        y: item.water_temperatures?.depth1
                    })).filter(point => point.y !== null),
                    borderColor: this.colors.danger,
                    backgroundColor: this.colors.danger + '20',
                    tension: 0.4
                },
                {
                    label: 'Depth 2',
                    data: data.map(item => ({
                        x: new Date(item.timestamp),
                        y: item.water_temperatures?.depth2
                    })).filter(point => point.y !== null),
                    borderColor: this.colors.warning,
                    backgroundColor: this.colors.warning + '20',
                    tension: 0.4
                },
                {
                    label: 'Depth 3',
                    data: data.map(item => ({
                        x: new Date(item.timestamp),
                        y: item.water_temperatures?.depth3
                    })).filter(point => point.y !== null),
                    borderColor: this.colors.success,
                    backgroundColor: this.colors.success + '20',
                    tension: 0.4
                }
            ]
        };
        
        const options = {
            ...this.defaultOptions,
            scales: {
                ...this.defaultOptions.scales,
                y: {
                    ...this.defaultOptions.scales.y,
                    title: {
                        display: true,
                        text: 'Water Temperature (°C)'
                    }
                }
            }
        };
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: options
        });
        
        return this.charts[canvasId];
    }
    
    // Create battery level chart (admin only)
    createBatteryChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const chartData = {
            datasets: [{
                label: 'Battery Level',
                data: data.map(item => ({
                    x: new Date(item.timestamp),
                    y: item.battery?.level
                })).filter(point => point.y !== null),
                borderColor: this.colors.success,
                backgroundColor: this.colors.success + '20',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: data.map(item => {
                    const level = item.battery?.level;
                    if (level >= 70) return this.colors.success;
                    if (level >= 30) return this.colors.warning;
                    return this.colors.danger;
                })
            }]
        };
        
        const options = {
            ...this.defaultOptions,
            scales: {
                ...this.defaultOptions.scales,
                y: {
                    ...this.defaultOptions.scales.y,
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Battery Level (%)'
                    }
                }
            }
        };
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: options
        });
        
        return this.charts[canvasId];
    }
    
    // Update existing chart with new data
    updateChart(chartId, newData) {
        const chart = this.charts[chartId];
        if (!chart) return false;
        
        // Update data based on chart type
        switch (chart.config.type) {
            case 'line':
                if (chart.data.datasets.length === 1) {
                    // Single dataset chart
                    chart.data.datasets[0].data = newData;
                } else {
                    // Multi-dataset chart (like water temperatures)
                    chart.data.datasets.forEach((dataset, index) => {
                        dataset.data = newData[index] || [];
                    });
                }
                break;
        }
        
        chart.update('none'); // Update without animation for real-time data
        return true;
    }
    
    // Destroy a chart
    destroyChart(chartId) {
        if (this.charts[chartId]) {
            this.charts[chartId].destroy();
            delete this.charts[chartId];
            return true;
        }
        return false;
    }
    
    // Destroy all charts
    destroyAllCharts() {
        Object.keys(this.charts).forEach(chartId => {
            this.destroyChart(chartId);
        });
    }
    
    // Get chart instance
    getChart(chartId) {
        return this.charts[chartId] || null;
    }
    
    // Utility method to format data for charts
    static formatDataForChart(rawData, valueField) {
        return rawData.map(item => ({
            x: new Date(item.timestamp),
            y: item[valueField]
        })).filter(point => point.y !== null && point.y !== undefined);
    }
    
    // Load and display chart data
    async loadChartData(chartType, canvasId, limit = 50) {
        try {
            const response = await fetch(`/api/get-sensor-data.php?limit=${limit}`);
            const result = await response.json();
            
            if (result.success) {
                const data = Array.isArray(result.data) ? result.data : [result.data];
                
                switch (chartType) {
                    case 'temperature':
                        return this.createTemperatureChart(canvasId, data);
                    case 'humidity':
                        return this.createHumidityChart(canvasId, data);
                    case 'water-level':
                        return this.createWaterLevelChart(canvasId, data);
                    case 'water-temperature':
                        return this.createWaterTemperatureChart(canvasId, data);
                    default:
                        console.error('Unknown chart type:', chartType);
                        return null;
                }
            } else {
                console.error('Failed to load chart data:', result.error);
                return null;
            }
        } catch (error) {
            console.error('Error loading chart data:', error);
            return null;
        }
    }
}

// Initialize charts system
window.blimasCharts = new BLIMASCharts();

// Utility function for backward compatibility
function createChart(type, canvasId, limit = 50) {
    return window.blimasCharts.loadChartData(type, canvasId, limit);
}