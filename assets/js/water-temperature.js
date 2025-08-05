// Water Temperature Chart functionality with Chart.js detection

class WaterTemperatureChart {
    constructor() {
        this.chart = null;
        this.currentPeriod = 'day';
        this.init();
    }

    init() {
        console.log('Water Temperature Chart initializing...');
        
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
            
            const apiUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api/get_water_temp_data.php';
            const url = `${apiUrl}?period=${this.currentPeriod}`;
            
            console.log('Fetching water temperature data from:', url);
            
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
            console.log('Water temperature data received:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }

            if (!data || data.length === 0) {
                throw new Error('No data available for the selected period');
            }

            this.renderChart(data);
            this.updateStats(data);
            
        } catch (error) {
            console.error('Failed to load water temperature data:', error);
            Utils.showError(`Failed to load water temperature data: ${error.message}`);
        } finally {
            Utils.toggleChartLoader(false);
        }
    }

    renderChart(data) {
        const ctx = document.getElementById('waterTempChart');
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
        const surfaceData = [];
        const midData = [];
        const bottomData = [];

        data.forEach(item => {
            const date = new Date(item.timestamp);
            labels.push(this.formatDateLabel(date));
            surfaceData.push(parseFloat(item.water_temp_depth1));
            midData.push(parseFloat(item.water_temp_depth2));
            bottomData.push(parseFloat(item.water_temp_depth3));
        });

        // Chart configuration
        const config = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Surface Temperature (°C)',
                        data: surfaceData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Mid Level Temperature (°C)',
                        data: midData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Bottom Temperature (°C)',
                        data: bottomData,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `Water Temperature at Different Depths - ${this.getPeriodLabel()}`,
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
                                return `${context.dataset.label}: ${context.parsed.y.toFixed(1)}°C`;
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
                            text: 'Temperature (°C)',
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
            console.error('Error creating water temperature chart:', error);
            Utils.showError('Failed to create water temperature chart. Please refresh the page.');
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

        // Surface temperature stats
        const surfaceTemps = data.map(item => parseFloat(item.water_temp_depth1)).filter(temp => !isNaN(temp));
        this.updateDepthStats('Surface', surfaceTemps);

        // Mid level temperature stats
        const midTemps = data.map(item => parseFloat(item.water_temp_depth2)).filter(temp => !isNaN(temp));
        this.updateDepthStats('Mid', midTemps);

        // Bottom temperature stats
        const bottomTemps = data.map(item => parseFloat(item.water_temp_depth3)).filter(temp => !isNaN(temp));
        this.updateDepthStats('Bottom', bottomTemps);
    }

    updateDepthStats(depth, temperatures) {
        if (temperatures.length === 0) return;

        const current = temperatures[temperatures.length - 1];
        const max = Math.max(...temperatures);
        const min = Math.min(...temperatures);
        const avg = temperatures.reduce((sum, temp) => sum + temp, 0) / temperatures.length;

        this.updateStatElement(`current${depth}`, current, '°C');
        this.updateStatElement(`max${depth}`, max, '°C');
        this.updateStatElement(`min${depth}`, min, '°C');
        this.updateStatElement(`avg${depth}`, avg, '°C');
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
    window.waterTempChart = new WaterTemperatureChart();
} else {
    console.error('Chart.js not available for water temperature chart');
}