/**
 * BLIMAS Real-time Data Updates
 * Enhanced JavaScript for real-time sensor and weather data updates
 */

class BlimasDataManager {
    constructor() {
        this.refreshInterval = 5000; // 5 seconds
        this.weatherRefreshInterval = 600000; // 10 minutes
        this.isOnline = true;
        this.lastUpdate = null;
        
        this.initializeUpdates();
        this.setupEventHandlers();
    }
    
    /**
     * Initialize data updates
     */
    initializeUpdates() {
        // Initial data load
        this.updateSensorData();
        this.updateWeatherData();
        
        // Set up intervals
        this.sensorInterval = setInterval(() => this.updateSensorData(), this.refreshInterval);
        this.weatherInterval = setInterval(() => this.updateWeatherData(), this.weatherRefreshInterval);
        
        // Update timestamp display
        setInterval(() => this.updateDateTime(), 1000);
    }
    
    /**
     * Update sensor data from API
     */
    async updateSensorData() {
        try {
            const response = await fetch('api/data.php?action=latest&nocache=' + Date.now());
            const result = await response.json();
            
            if (result.success && result.data) {
                this.displaySensorData(result.data);
                this.updateSystemStatus('online');
                this.lastUpdate = new Date();
            } else {
                console.error('Sensor data error:', result.error);
                this.updateSystemStatus('error');
            }
        } catch (error) {
            console.error('Failed to fetch sensor data:', error);
            this.updateSystemStatus('offline');
        }
    }
    
    /**
     * Update weather data from API
     */
    async updateWeatherData() {
        try {
            const response = await fetch('api/data.php?action=weather&nocache=' + Date.now());
            const result = await response.json();
            
            if (result.success && result.data) {
                this.displayWeatherData(result.data);
            } else {
                console.error('Weather data error:', result.error);
            }
        } catch (error) {
            console.error('Failed to fetch weather data:', error);
        }
    }
    
    /**
     * Display sensor data on homepage
     */
    displaySensorData(data) {
        const elements = {
            'temp1': data.water_temp1,
            'temp2': data.water_temp2, 
            'temp3': data.water_temp3,
            'tempDHT': data.air_temperature,
            'humhum': data.humidity,
            'distance': data.water_level
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element && value !== null && value !== undefined) {
                let displayValue;
                let unit;
                
                switch (id) {
                    case 'temp1':
                    case 'temp2':
                    case 'temp3':
                    case 'tempDHT':
                        displayValue = parseFloat(value).toFixed(1);
                        unit = '°C';
                        break;
                    case 'humhum':
                        displayValue = parseFloat(value).toFixed(1);
                        unit = '%';
                        break;
                    case 'distance':
                        displayValue = parseFloat(value).toFixed(1);
                        unit = 'cm';
                        break;
                    default:
                        displayValue = parseFloat(value).toFixed(1);
                        unit = '';
                }
                
                element.textContent = `${displayValue} ${unit}`;
                
                // Add animation class
                element.classList.add('data-updated');
                setTimeout(() => element.classList.remove('data-updated'), 300);
            }
        });
        
        // Update battery level if available
        if (data.battery_level && document.getElementById('battery')) {
            const batteryElement = document.getElementById('battery');
            batteryElement.textContent = `${parseFloat(data.battery_level).toFixed(1)}%`;
            
            // Add battery level indicator class
            const batteryLevel = parseFloat(data.battery_level);
            batteryElement.className = 'sensor-value';
            if (batteryLevel < 20) {
                batteryElement.classList.add('battery-low');
            } else if (batteryLevel < 50) {
                batteryElement.classList.add('battery-medium');
            } else {
                batteryElement.classList.add('battery-high');
            }
        }
    }
    
    /**
     * Display weather data
     */
    displayWeatherData(data) {
        const weatherElements = {
            'temperature': `${data.temperature}°C`,
            'humidity': `${data.humidity}%`,
            'wind': `${data.wind_speed} kph`,
            'condition': data.weather_description,
            'rain': `${data.precipitation} mm`,
            'raini': `${data.precipitation} mm`,
            'rainut': `${data.temperature}°C`
        };
        
        Object.entries(weatherElements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
                element.classList.add('data-updated');
                setTimeout(() => element.classList.remove('data-updated'), 300);
            }
        });
        
        // Update location if available
        if (data.location && document.getElementById('location')) {
            document.getElementById('location').textContent = data.location;
        }
    }
    
    /**
     * Update date and time display
     */
    updateDateTime() {
        const now = new Date();
        
        // Update date
        const dateElement = document.getElementById('date');
        if (dateElement) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            dateElement.textContent = now.toLocaleDateString('en-US', options);
        }
        
        // Update time
        const timeElement = document.getElementById('time');
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString();
        }
    }
    
    /**
     * Update system status indicator
     */
    updateSystemStatus(status) {
        this.isOnline = (status === 'online');
        
        // Update status indicators
        const statusElements = document.querySelectorAll('.system-status');
        statusElements.forEach(element => {
            element.className = `system-status status-${status}`;
            element.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        });
        
        // Update connection indicator if exists
        const connectionIndicator = document.getElementById('connection-status');
        if (connectionIndicator) {
            connectionIndicator.className = `connection-indicator ${status}`;
            connectionIndicator.title = `System Status: ${status}`;
        }
    }
    
    /**
     * Setup event handlers
     */
    setupEventHandlers() {
        // Manual refresh button
        const refreshButton = document.getElementById('refresh-data');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                this.updateSensorData();
                this.updateWeatherData();
            });
        }
        
        // Page visibility change handling
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Page is hidden, reduce update frequency
                clearInterval(this.sensorInterval);
                this.sensorInterval = setInterval(() => this.updateSensorData(), this.refreshInterval * 2);
            } else {
                // Page is visible, restore normal frequency
                clearInterval(this.sensorInterval);
                this.sensorInterval = setInterval(() => this.updateSensorData(), this.refreshInterval);
                // Immediate update when page becomes visible
                this.updateSensorData();
            }
        });
        
        // Window focus handling
        window.addEventListener('focus', () => {
            // Immediate update when window regains focus
            this.updateSensorData();
            this.updateWeatherData();
        });
    }
    
    /**
     * Cleanup intervals
     */
    destroy() {
        if (this.sensorInterval) clearInterval(this.sensorInterval);
        if (this.weatherInterval) clearInterval(this.weatherInterval);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.blimasDataManager = new BlimasDataManager();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.blimasDataManager) {
        window.blimasDataManager.destroy();
    }
});

// Legacy compatibility function for existing code
async function fetchData() {
    if (window.blimasDataManager) {
        return window.blimasDataManager.updateSensorData();
    }
}

// Legacy compatibility for weather function
async function checkWeather() {
    if (window.blimasDataManager) {
        return window.blimasDataManager.updateWeatherData();
    }
}