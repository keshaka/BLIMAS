// BLIMAS Main JavaScript Module
class BLIMAS {
    constructor() {
        this.config = {
            dataRefreshInterval: 30000, // 30 seconds
            chartUpdateInterval: 60000,  // 1 minute
            weatherUpdateInterval: 600000 // 10 minutes
        };
        
        this.sensors = {
            current: null,
            history: []
        };
        
        this.weather = {
            current: null,
            lastUpdate: null
        };
        
        this.init();
    }
    
    init() {
        console.log('BLIMAS system initializing...');
        
        // Load initial data
        this.loadSensorData();
        this.loadWeatherData();
        
        // Set up periodic updates
        this.startDataRefresh();
        
        // Initialize UI components
        this.initDateTime();
        this.initLoadingIndicators();
        
        console.log('BLIMAS system initialized');
    }
    
    // Data Loading Methods
    async loadSensorData(limit = 1) {
        try {
            const response = await fetch(`/api/get-sensor-data.php?limit=${limit}&nocache=${Date.now()}`);
            const result = await response.json();
            
            if (result.success) {
                this.sensors.current = result.data;
                this.updateSensorDisplay();
                return result.data;
            } else {
                console.error('Failed to load sensor data:', result.error);
                this.showError('Failed to load sensor data');
            }
        } catch (error) {
            console.error('Error loading sensor data:', error);
            this.showError('Network error loading sensor data');
        }
    }
    
    async loadWeatherData() {
        try {
            const response = await fetch(`/api/get-weather.php?nocache=${Date.now()}`);
            const result = await response.json();
            
            if (result.success) {
                this.weather.current = result;
                this.weather.lastUpdate = new Date();
                this.updateWeatherDisplay();
                return result;
            } else {
                console.error('Failed to load weather data:', result.error);
                this.showError('Failed to load weather data');
            }
        } catch (error) {
            console.error('Error loading weather data:', error);
            this.showError('Network error loading weather data');
        }
    }
    
    // Display Update Methods
    updateSensorDisplay() {
        const data = this.sensors.current;
        if (!data) return;
        
        // Update sensor values on the page
        this.updateElement('tempDHT', data.air_temperature, '°C');
        this.updateElement('humhum', data.humidity, '%');
        this.updateElement('distance', data.water_level, 'cm');
        
        if (data.water_temperatures) {
            this.updateElement('temp1', data.water_temperatures.depth1, '°C');
            this.updateElement('temp2', data.water_temperatures.depth2, '°C');
            this.updateElement('temp3', data.water_temperatures.depth3, '°C');
        }
        
        // Update last update time
        this.updateElement('lastUpdate', this.formatDateTime(new Date(data.timestamp)));
        
        console.log('Sensor display updated');
    }
    
    updateWeatherDisplay() {
        const weather = this.weather.current;
        if (!weather) return;
        
        this.updateElement('temperature', weather.temperature, '°C');
        this.updateElement('humidity', weather.humidity, '%');
        this.updateElement('wind', weather.wind_speed, 'm/s');
        this.updateElement('condition', weather.description);
        this.updateElement('rain', weather.clouds, '%');
        
        console.log('Weather display updated');
    }
    
    updateElement(id, value, unit = '') {
        const element = document.getElementById(id);
        if (element) {
            if (value !== null && value !== undefined) {
                element.textContent = `${value}${unit}`;
                element.classList.remove('loading', 'error');
            } else {
                element.textContent = 'N/A';
                element.classList.add('error');
            }
        }
    }
    
    // DateTime Management
    initDateTime() {
        this.updateDateTime();
        setInterval(() => this.updateDateTime(), 1000);
    }
    
    updateDateTime() {
        const now = new Date();
        
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        
        const formattedDate = now.toLocaleDateString('en-US', options);
        const formattedTime = now.toLocaleTimeString();
        
        this.updateElement('date', formattedDate);
        this.updateElement('time', formattedTime);
    }
    
    formatDateTime(date) {
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Loading Indicators
    initLoadingIndicators() {
        const elements = ['tempDHT', 'humhum', 'distance', 'temp1', 'temp2', 'temp3', 
                         'temperature', 'wind', 'condition', 'rain'];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element && element.textContent === 'Loading...') {
                element.classList.add('loading');
            }
        });
    }
    
    // Periodic Updates
    startDataRefresh() {
        // Refresh sensor data
        setInterval(() => {
            this.loadSensorData();
        }, this.config.dataRefreshInterval);
        
        // Refresh weather data
        setInterval(() => {
            this.loadWeatherData();
        }, this.config.weatherUpdateInterval);
    }
    
    // Error Handling
    showError(message) {
        console.error('BLIMAS Error:', message);
        
        // You can implement a toast notification system here
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-toast';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 9999;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        `;
        
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
    
    // Utility Methods
    static formatNumber(value, decimals = 1) {
        if (value === null || value === undefined) return 'N/A';
        return Number(value).toFixed(decimals);
    }
    
    static getStatusClass(value, thresholds) {
        if (value >= thresholds.good) return 'status-good';
        if (value >= thresholds.warning) return 'status-warning';
        return 'status-critical';
    }
}

// Initialize BLIMAS when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.blimas = new BLIMAS();
});

// Backward compatibility with existing code
async function fetchData() {
    if (window.blimas) {
        return await window.blimas.loadSensorData();
    }
}

function updateDateTime() {
    if (window.blimas) {
        window.blimas.updateDateTime();
    }
}