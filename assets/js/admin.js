// BLIMAS Admin Dashboard JavaScript
class BLIMASAdmin {
    constructor() {
        this.refreshInterval = 30000; // 30 seconds
        this.alertThresholds = {
            battery: {
                critical: 20,
                warning: 40
            },
            temperature: {
                min: 15,
                max: 35
            },
            humidity: {
                min: 30,
                max: 90
            }
        };
        
        this.init();
    }
    
    init() {
        console.log('BLIMAS Admin system initializing...');
        
        // Initialize notifications
        this.initNotifications();
        
        // Start monitoring
        this.startMonitoring();
        
        console.log('BLIMAS Admin system initialized');
    }
    
    // Notification system
    initNotifications() {
        // Request notification permission
        if ("Notification" in window) {
            if (Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }
        }
    }
    
    showNotification(title, message, type = 'info') {
        // Browser notification
        if ("Notification" in window && Notification.permission === "granted") {
            const notification = new Notification(title, {
                body: message,
                icon: '/images/logo.png',
                badge: '/images/logo.png'
            });
            
            setTimeout(() => {
                notification.close();
            }, 5000);
        }
        
        // In-page notification
        this.showAlert(message, type);
    }
    
    showAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <i class="fa fa-${this.getAlertIcon(type)}"></i>
            ${message}
            <button onclick="this.parentElement.remove()" class="alert-close">&times;</button>
        `;
        
        const container = document.querySelector('.admin-content');
        if (container) {
            container.insertBefore(alert, container.firstChild);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 5000);
        }
    }
    
    getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'error': 'times-circle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    // Data monitoring
    async startMonitoring() {
        await this.checkSystemHealth();
        
        // Set up periodic checks
        setInterval(() => {
            this.checkSystemHealth();
        }, this.refreshInterval);
    }
    
    async checkSystemHealth() {
        try {
            const response = await fetch('/api/admin-data.php?limit=1');
            const result = await response.json();
            
            if (result.success) {
                this.analyzeData(result.data);
                this.updateSystemStatus('online');
            } else {
                this.updateSystemStatus('error');
                console.error('Failed to get system data:', result.error);
            }
        } catch (error) {
            this.updateSystemStatus('offline');
            console.error('System health check failed:', error);
        }
    }
    
    analyzeData(data) {
        // Check battery level
        if (data.battery && data.battery.level !== null) {
            const batteryLevel = data.battery.level;
            
            if (batteryLevel <= this.alertThresholds.battery.critical) {
                this.showNotification(
                    'Critical Battery Alert',
                    `Battery level is critically low: ${batteryLevel}%`,
                    'error'
                );
            } else if (batteryLevel <= this.alertThresholds.battery.warning) {
                this.showNotification(
                    'Battery Warning',
                    `Battery level is low: ${batteryLevel}%`,
                    'warning'
                );
            }
        }
        
        // Check temperature ranges
        if (data.air_temperature !== null) {
            const temp = data.air_temperature;
            if (temp < this.alertThresholds.temperature.min || temp > this.alertThresholds.temperature.max) {
                this.showNotification(
                    'Temperature Alert',
                    `Air temperature is outside normal range: ${temp}°C`,
                    'warning'
                );
            }
        }
        
        // Check humidity levels
        if (data.humidity !== null) {
            const humidity = data.humidity;
            if (humidity < this.alertThresholds.humidity.min || humidity > this.alertThresholds.humidity.max) {
                this.showNotification(
                    'Humidity Alert',
                    `Humidity is outside normal range: ${humidity}%`,
                    'warning'
                );
            }
        }
        
        // Check data freshness
        const lastUpdate = new Date(data.timestamp);
        const now = new Date();
        const minutesSinceUpdate = (now - lastUpdate) / (1000 * 60);
        
        if (minutesSinceUpdate > 10) {
            this.showNotification(
                'Data Freshness Alert',
                `Last data update was ${Math.round(minutesSinceUpdate)} minutes ago`,
                'warning'
            );
        }
    }
    
    updateSystemStatus(status) {
        const statusIndicator = document.getElementById('systemStatus');
        if (statusIndicator) {
            statusIndicator.className = `system-status status-${status}`;
            
            const statusText = {
                'online': 'System Online',
                'offline': 'System Offline',
                'error': 'System Error'
            };
            
            statusIndicator.textContent = statusText[status] || 'Unknown';
        }
    }
    
    // Data export utilities
    exportData(format = 'csv', days = 30) {
        const url = `/admin/export-data.php?format=${format}&days=${days}`;
        window.open(url, '_blank');
    }
    
    // Chart management
    createBatteryTrendChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const batteryData = data
            .filter(item => item.battery && item.battery.level !== null)
            .map(item => ({
                x: new Date(item.timestamp),
                y: item.battery.level
            }));
        
        return new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Battery Level (%)',
                    data: batteryData,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: batteryData.map(point => {
                        if (point.y <= 20) return '#e74c3c';
                        if (point.y <= 40) return '#f39c12';
                        return '#27ae60';
                    })
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Battery: ${context.parsed.y}%`;
                            }
                        }
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
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Battery Level (%)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // System maintenance
    async optimizeDatabase() {
        try {
            const response = await fetch('/admin/maintenance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=optimize'
            });
            
            const result = await response.text();
            this.showAlert('Database optimization completed', 'success');
            return result;
        } catch (error) {
            this.showAlert('Database optimization failed', 'error');
            throw error;
        }
    }
    
    // Real-time updates
    enableRealTimeUpdates() {
        // Enable auto-refresh for dashboard cards
        setInterval(() => {
            this.refreshDashboardData();
        }, this.refreshInterval);
    }
    
    async refreshDashboardData() {
        const event = new CustomEvent('dashboardRefresh');
        document.dispatchEvent(event);
    }
    
    // Utility methods
    formatTimestamp(timestamp) {
        return new Date(timestamp).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
    
    formatFileSize(bytes) {
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        if (bytes === 0) return '0 Bytes';
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    // Configuration management
    updateAlertThresholds(newThresholds) {
        this.alertThresholds = { ...this.alertThresholds, ...newThresholds };
        localStorage.setItem('blimasAdminConfig', JSON.stringify(this.alertThresholds));
    }
    
    loadSavedConfig() {
        const saved = localStorage.getItem('blimasAdminConfig');
        if (saved) {
            try {
                this.alertThresholds = { ...this.alertThresholds, ...JSON.parse(saved) };
            } catch (error) {
                console.error('Failed to load saved config:', error);
            }
        }
    }
}

// Initialize admin system when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.blimasAdmin = new BLIMASAdmin();
    
    // Enable real-time updates
    window.blimasAdmin.enableRealTimeUpdates();
    
    // Load saved configuration
    window.blimasAdmin.loadSavedConfig();
});

// Global utility functions for admin interface
function refreshData() {
    if (window.blimasAdmin) {
        window.blimasAdmin.refreshDashboardData();
        window.blimasAdmin.showAlert('Data refreshed successfully', 'success');
    }
}

function exportData(format = 'csv') {
    if (window.blimasAdmin) {
        window.blimasAdmin.exportData(format);
    }
}

function testAlert() {
    if (window.blimasAdmin) {
        window.blimasAdmin.showNotification(
            'Test Alert',
            'This is a test notification from the BLIMAS admin system.',
            'info'
        );
    }
}