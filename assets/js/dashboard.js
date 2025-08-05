// Dashboard functionality for real-time data display

class Dashboard {
    constructor() {
        this.updateInterval = 5 * 60 * 1000; // 5 minutes
        this.refreshTimer = null;
        this.init();
    }

    init() {
        console.log('Dashboard initializing...');
        this.loadLatestData();
        this.startAutoRefresh();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Refresh button if exists
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadLatestData();
            });
        }

        // Manual refresh on focus
        window.addEventListener('focus', () => {
            this.loadLatestData();
        });

        // Add manual refresh button to page
        this.addRefreshButton();
    }

    addRefreshButton() {
        const dataSection = document.getElementById('data-section');
        if (dataSection) {
            const refreshBtn = document.createElement('button');
            refreshBtn.className = 'btn btn-outline-primary mb-3';
            refreshBtn.id = 'manualRefresh';
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Refresh Data';
            refreshBtn.onclick = () => this.loadLatestData();
            
            const container = dataSection.querySelector('.container');
            if (container) {
                container.insertBefore(refreshBtn, container.firstChild.nextSibling);
            }
        }
    }

    async loadLatestData() {
        console.log('Loading latest data...');
        try {
            this.showLoadingState();
            
            // Construct the correct API URL
            const apiUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api/get_latest_data.php';
            console.log('API URL:', apiUrl);
            
            const response = await fetch(apiUrl, {
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
            console.log('Received data:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }

            this.updateDataCards(data);
            this.updateLastUpdateTime(data.timestamp);
            this.hideLoadingState();
            
            Utils.showSuccess('Data updated successfully!');
            
        } catch (error) {
            console.error('Failed to load latest data:', error);
            this.showErrorState();
            Utils.showError(`Failed to load sensor data: ${error.message}`);
            this.hideLoadingState();
        }
    }

    updateDataCards(data) {
        console.log('Updating data cards with:', data);
        
        // Air Temperature
        this.updateCard('airTemp', data.air_temperature);
        
        // Humidity
        this.updateCard('humidity', data.humidity);
        
        // Water Level - Fixed the field name
        this.updateCard('waterLevel', data.water_level);
        
        // Water Temperatures - Fixed the field names
        this.updateCard('waterTemp1', data.water_temp_depth1);
        this.updateCard('waterTemp2', data.water_temp_depth2);
        this.updateCard('waterTemp3', data.water_temp_depth3);

        // Add animation to updated cards
        this.animateCards();
    }

    updateCard(elementId, value) {
        const element = document.getElementById(elementId);
        console.log(`Updating ${elementId} with value:`, value);
        
        if (element) {
            const formattedValue = this.formatValue(value);
            element.textContent = formattedValue;
            
            // Add update animation
            element.classList.add('pulse-animation');
            setTimeout(() => {
                element.classList.remove('pulse-animation');
            }, 1000);
        } else {
            console.warn(`Element with ID ${elementId} not found`);
        }
    }

    formatValue(value) {
        if (value === null || value === undefined || isNaN(value)) {
            return '--';
        }
        return Number(value).toFixed(1);
    }

    updateLastUpdateTime(timestamp) {
        const element = document.getElementById('lastUpdate');
        if (element) {
            const date = new Date(timestamp);
            element.textContent = date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }

    animateCards() {
        const cards = document.querySelectorAll('.data-card');
        cards.forEach((card, index) => {
            card.style.animation = 'none';
            setTimeout(() => {
                card.style.animation = 'pulse 0.5s ease-in-out';
            }, index * 100);
        });
    }

    showLoadingState() {
        const cards = document.querySelectorAll('.data-card');
        cards.forEach(card => card.classList.add('loading'));
        
        const refreshBtn = document.getElementById('manualRefresh');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
        }
    }

    hideLoadingState() {
        const cards = document.querySelectorAll('.data-card');
        cards.forEach(card => card.classList.remove('loading'));
        
        const refreshBtn = document.getElementById('manualRefresh');
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Refresh Data';
        }
    }

    showErrorState() {
        const elements = ['airTemp', 'humidity', 'waterLevel', 'waterTemp1', 'waterTemp2', 'waterTemp3'];
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = '--';
            }
        });
    }

    startAutoRefresh() {
        this.refreshTimer = setInterval(() => {
            console.log('Auto-refreshing data...');
            this.loadLatestData();
        }, this.updateInterval);
    }

    stopAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing dashboard...');
    window.dashboard = new Dashboard();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.dashboard) {
        window.dashboard.stopAutoRefresh();
    }
});