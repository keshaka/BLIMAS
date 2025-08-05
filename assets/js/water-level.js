// Water Level Chart functionality with Chart.js detection

class WaterLevelChart {
    constructor() {
        this.chart = null;
        this.currentPeriod = 'day';
        this.init();
    }

    init() {
        console.log('Water Level Chart initializing...');
        
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
            const url = `${apiUrl}?type=water_level&period=${this.currentPeriod}`;
            
            console.log('Fetching water level data from:', url);
            
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
            console.log('Water level data received:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }

            if (!data || data.length === 0) {
                throw new Error('No data available for the selected period');
            }

            this.renderChart(data);
            this.updateStats(data);
            
        } catch (error) {
            console.error('Failed to load water level data:', error);
            Utils.showError(`Failed to load water level data: ${error.message}`);
        } finally {
            Utils.toggleChartLoader(false);
        }
    }

    renderChart(data) {
        const ctx = document.getElementById('waterLevelChart');
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
            chartData.push(parseFloat(item.water_level));
        });

        // Chart configuration
        const config = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Water Level (m)',
                    data: chartData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fee715',
                    pointBorderColor: '#10b981',
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
                        text: `Water Level Monitoring - ${this.getPeriodLabel()}`,
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
                                return `Water Level: ${context.parsed.y.toFixed(2)}m`;
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
                            text: 'Water Level (meters)',
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
            console.error('Error creating water level chart:', error);
            Utils.showError('Failed to create water level chart. Please refresh the page.');
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

        const levels = data.map(item => parseFloat(item.water_level)).filter(level => !isNaN(level));
        
        if (levels.length === 0) return;

        const current = levels[levels.length - 1];
        const max = Math.max(...levels);
        const min = Math.min(...levels);
        const avg = levels.reduce((sum, level) => sum + level, 0) / levels.length;

        this.updateStatElement('currentLevel', current, ' m');
        this.updateStatElement('maxLevel', max, ' m');
        this.updateStatElement('minLevel', min, ' m');
        this.updateStatElement('avgLevel', avg, ' m');
    }

    updateStatElement(elementId, value, unit) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = Utils.formatNumber(value, 2) + unit;
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
    window.waterLevelChart = new WaterLevelChart();
} else {
    console.error('Chart.js not available for water level chart');
}