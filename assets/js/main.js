// Main JavaScript file for BLIMAS

// Initialize AOS (Animate On Scroll)
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
});

// Loader functionality
window.addEventListener('load', function() {
    const loader = document.getElementById('loader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }, 1000);
    }
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Utility functions
const Utils = {
    // Format timestamp to readable format
    formatTimestamp: function(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Format number with proper decimals
    formatNumber: function(num, decimals = 1) {
        if (num === null || num === undefined || isNaN(num)) {
            return '--';
        }
        return Number(num).toFixed(decimals);
    },

    // Show loading state
    showLoading: function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.add('loading');
        }
    },

    // Hide loading state
    hideLoading: function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove('loading');
        }
    },

    // Show/hide chart loader
    toggleChartLoader: function(show = true) {
        const loader = document.getElementById('chartLoader');
        if (loader) {
            loader.style.display = show ? 'flex' : 'none';
        }
    },

    // API call wrapper with better error handling
    apiCall: async function(url, options = {}) {
        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            throw error;
        }
    },

    // Show error message with new styling
    showError: function(message, containerId = 'main') {
        const container = document.getElementById(containerId) || document.body;
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.style.position = 'fixed';
        alert.style.top = '100px';
        alert.style.right = '20px';
        alert.style.zIndex = '10000';
        alert.style.maxWidth = '400px';
        alert.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    },

    // Show success message with new styling
    showSuccess: function(message, containerId = 'main') {
        const container = document.getElementById(containerId) || document.body;
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.style.position = 'fixed';
        alert.style.top = '100px';
        alert.style.right = '20px';
        alert.style.zIndex = '10000';
        alert.style.maxWidth = '400px';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }
};

// Chart configuration defaults with new color scheme
const ChartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
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
            titleFont: {
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                size: 12
            },
            callbacks: {
                title: function(context) {
                    const date = new Date(context[0].label);
                    return date.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }
    },
    scales: {
        x: {
            type: 'time',
            time: {
                displayFormats: {
                    hour: 'HH:mm',
                    day: 'MM/DD',
                    week: 'MM/DD',
                    month: 'MM/YY'
                }
            },
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
                color: '#101820',
                font: {
                    weight: 'bold'
                }
            }
        },
        y: {
            beginAtZero: false,
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
};

// Updated color palette for charts with high contrast
const ChartColors = {
    primary: '#101820',
    secondary: '#fee715',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#3b82f6',
    surface: '#3b82f6',
    mid: '#f59e0b',
    bottom: '#ef4444',
    // Additional colors for better contrast
    darkBlue: '#1e40af',
    brightGreen: '#22c55e',
    brightOrange: '#ff8c00',
    brightRed: '#dc2626',
    brightPurple: '#9333ea'
};

// Export for use in other files
window.Utils = Utils;
window.ChartDefaults = ChartDefaults;
window.ChartColors = ChartColors;

// Add custom styles for better visibility
const customStyles = `
    .chart-container canvas {
        border: 2px solid #fee715 !important;
        border-radius: 10px !important;
    }
    
    .navbar.scrolled {
        background: rgba(16, 24, 32, 0.98) !important;
        box-shadow: 0 2px 20px rgba(254, 231, 21, 0.3) !important;
    }
    
    .data-card:hover {
        box-shadow: 0 20px 40px rgba(254, 231, 21, 0.2) !important;
    }
    
    .floating-card {
        box-shadow: 0 10px 30px rgba(254, 231, 21, 0.3) !important;
    }
`;

// Inject custom styles
const styleSheet = document.createElement('style');
styleSheet.textContent = customStyles;
document.head.appendChild(styleSheet);

// Improve visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add focus styles for better accessibility
    const focusStyles = `
        *:focus {
            outline: 2px solid #fee715 !important;
            outline-offset: 2px !important;
        }
        
        .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(254, 231, 21, 0.5) !important;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #fee715 !important;
            box-shadow: 0 0 0 0.2rem rgba(254, 231, 21, 0.25) !important;
        }
    `;
    
    const accessibilityStyles = document.createElement('style');
    accessibilityStyles.textContent = focusStyles;
    document.head.appendChild(accessibilityStyles);
});