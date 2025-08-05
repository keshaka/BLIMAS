// Humidity Chart functionality with Chart.js detection

class HumidityChart {
    constructor() {
        this.chart = null;
        this.currentPeriod = 'day';
        this.init();
    }

    init() {
        console.log('Humidity Chart initializing...');
        
        // Check if Chart.js is available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded!');
            Utils.showError('Chart library failed to load. Please refresh the page.');
            return;
        }
        
        this.setupEventListeners();
        this.loadChart();
    }

    setupEventListeners() {
        const periodSelect = document.getElementById('periodSelect');
        if (periodSelect) {
            periodSelect.addEventListener('change', (e) => {
                this.currentPeriod = e.target.value;
                this.loadChart();
            });
        }
    }

    async loadChart() {
        try {
            Utils.toggleChartLoader(true);
            
            const apiUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api/get_historical_data.php';
            const url = `${apiUrl}?type=humidity&period=${this.currentPeriod}`;
            
            console.log('Fetching humidity data from:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                cache: 'no-cache'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Humidity data received:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }

            if (!data || data.length === 0) {
                throw new Error('No data available for the selected period');
            }

            this.renderChart(data);
            this.updateStats(data);
            
        } catch (error) {
            console.error('Failed to load humidity data:', error);
            Utils.showError(`Failed to load humidity data: ${error.message}`);
        } finally {
            Utils.toggleChartLoader(false);
        }
    }

    renderChart(data) {
        const ctx = document.getElementById('humidityChart');
        if (!ctx) {
            console.error('Chart canvas not found');
            return;
        }

        // Destroy existing chart
        if (this.chart) {
            this.chart.destroy();
        }

        // Prepare labels and data
        const labels = [];
        const chartData = [];

        data.forEach(item => {
            const date = new Date(item.timestamp);
            labels.push(this.formatDateLabel(date));
            chartData.push(parseFloat(item.humidity));
        });

        // Chart configuration
        const config = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Humidity (%)',
                    data: chartData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fee715',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `Humidity Levels - ${this.getPeriodLabel()}`,
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        color: '#101820'
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            color: '#101820'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(16, 24, 32, 0.95)',
                        titleColor: '#fee715',
                        bodyColor: '#ffffff',
                        borderColor: '#fee715',
                        borderWidth: 2,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return `Humidity: ${context.parsed.y.toFixed(1)}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: true,
                            color: 'rgba(16, 24, 32, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#101820',
                            maxTicksLimit: 10
                        },
                        title: {
                            display: true,
                            text: 'Time',
                            color: '#101820',
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        display: true,
                        min: 0,
                        max: 100,
                        grid: {
                            display: true,
                            color: 'rgba(16, 24, 32, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#101820'
                        },
                        title: {
                            display: true,
                            text: 'Humidity (%)',
                            color: '#101820',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 8,
                        borderWidth: 2
                    },
                    line: {
                        tension: 0.4,
                        borderWidth: 3
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        };

        try {
            this.chart = new Chart(ctx, config);
        } catch (error) {
            console.error('Error creating humidity chart:', error);
            Utils.showError('Failed to create humidity chart. Please refresh the page.');
        }
    }

    formatDateLabel(date) {
        switch (this.currentPeriod) {
            case 'day':
                return date.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            case 'week':
                return date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit'
                });
            case 'month':
                return date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric' 
                });
            default:
                return date.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
        }
    }

    updateStats(data) {
        if (data.length === 0) return;

        const humidityValues = data.map(item => parseFloat(item.humidity)).filter(val => !isNaN(val));
        
        if (humidityValues.length === 0) return;

        const current = humidityValues[humidityValues.length - 1];
        const max = Math.max(...humidityValues);
        const min = Math.min(...humidityValues);
        const avg = humidityValues.reduce((sum, val) => sum + val, 0) / humidityValues.length;

        this.updateStatElement('currentHumidity', current, '%');
        this.updateStatElement('maxHumidity', max, '%');
        this.updateStatElement('minHumidity', min, '%');
        this.updateStatElement('avgHumidity', avg, '%');
    }

    updateStatElement(elementId, value, unit) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = Utils.formatNumber(value) + unit;
        }
    }

    getPeriodLabel() {
        switch (this.currentPeriod) {
            case 'day': return 'Today';
            case 'week': return 'Last 7 Days';
            case 'month': return 'Last 30 Days';
            default: return 'Today';
        }
    }
}

// Initialize only if Chart.js is available
if (typeof Chart !== 'undefined') {
    window.humidityChart = new HumidityChart();
} else {
    console.error('Chart.js not available for humidity chart');
}