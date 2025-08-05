// AI Analysis functionality with improved error handling

class AnalysisManager {
    constructor() {
        this.currentAnalysis = null;
        this.init();
    }

    init() {
        console.log('Analysis Manager initializing...');
        this.setupEventListeners();
    }

    setupEventListeners() {
        const generateBtn = document.getElementById('generateAnalysis');
        if (generateBtn) {
            generateBtn.addEventListener('click', () => {
                this.generateAnalysis();
            });
        }
    }

    async generateAnalysis() {
        const analysisType = document.getElementById('analysisType').value;
        const analysisPeriod = document.getElementById('analysisPeriod').value;

        console.log('Generating analysis:', { type: analysisType, period: analysisPeriod });

        try {
            this.showLoading();
            this.hideResults();
            this.hideGettingStarted();

            // Construct API URL
            const apiUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api/generate_analysis.php';
            const url = `${apiUrl}?type=${analysisType}&period=${analysisPeriod}`;

            console.log('Fetching analysis from:', url);

            // Add timeout to the fetch request
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 45000); // 45 second timeout

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                cache: 'no-cache',
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Analysis data received:', data);

            if (data.error) {
                throw new Error(data.error);
            }

            if (data.success && data.analysis) {
                this.displayAnalysis(data);
                this.displayStats(data.stats);
                
                // Show note if using mock data
                if (data.note) {
                    Utils.showError(data.note);
                }
                
            } else {
                throw new Error('Invalid response format');
            }

        } catch (error) {
            console.error('Failed to generate analysis:', error);
            
            if (error.name === 'AbortError') {
                this.showError('Request timeout. The analysis is taking too long to generate. Please try again.');
            } else {
                this.showError(error.message);
            }
        } finally {
            this.hideLoading();
        }
    }

    displayAnalysis(data) {
        const container = document.getElementById('analysisContainer');
        const content = document.getElementById('analysisContent');
        const timestamp = document.getElementById('analysisTimestamp');

        if (!container || !content || !timestamp) return;

        // Format and display the analysis
        const formattedAnalysis = this.formatAnalysisText(data.analysis);

        // FIX: Remove innerHTML truncation by always using innerHTML and not innerText.
        // Also, wrap content in a scrollable box if needed.
        content.innerHTML = `<div class="analysis-scrollable">${formattedAnalysis}</div>`;

        // Optionally, you can add CSS to .analysis-scrollable for max-height/overflow-y:scroll for very large output

        // Update timestamp
        const date = new Date(data.generated_at);
        timestamp.textContent = date.toLocaleString();

        // Add method indicator if using mock data
        if (data.method === 'mock_fallback') {
            const methodIndicator = document.createElement('div');
            methodIndicator.className = 'alert alert-info';
            methodIndicator.innerHTML = '<i class="fas fa-info-circle me-2"></i><strong>Demo Mode:</strong> This is a sample analysis. Configure your Gemini API key for AI-powered insights.';
            content.insertBefore(methodIndicator, content.firstChild);
        }

        container.style.display = 'block';
        container.classList.add('fade-in');
        this.currentAnalysis = data;
    }

    displayStats(stats) {
        const statsContainer = document.getElementById('analysisStats');
        const statsCards = document.getElementById('statsCards');

        if (!statsContainer || !statsCards || !stats) return;

        const cards = [
            {
                title: 'Total Readings',
                value: stats.total_readings,
                icon: 'fas fa-database',
                color: 'primary'
            },
            {
                title: 'Avg Air Temp',
                value: `${parseFloat(stats.avg_air_temp).toFixed(1)}°C`,
                icon: 'fas fa-thermometer-half',
                color: 'danger'
            },
            {
                title: 'Avg Humidity',
                value: `${parseFloat(stats.avg_humidity).toFixed(1)}%`,
                icon: 'fas fa-tint',
                color: 'info'
            },
            {
                title: 'Avg Water Level',
                value: `${parseFloat(stats.avg_water_level).toFixed(2)}m`,
                icon: 'fas fa-water',
                color: 'success'
            },
            {
                title: 'Surface Temp',
                value: `${parseFloat(stats.avg_surface_temp).toFixed(1)}°C`,
                icon: 'fas fa-temperature-low',
                color: 'warning'
            }
        ];

        let cardsHTML = '';
        cards.forEach(card => {
            cardsHTML += `
                <div class="col-lg col-md-4 col-sm-6 mb-3">
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini text-${card.color}">
                            <i class="${card.icon}"></i>
                        </div>
                        <div class="stat-content-mini">
                            <h6>${card.title}</h6>
                            <span class="stat-value-mini">${card.value}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        statsCards.innerHTML = cardsHTML;
        statsContainer.style.display = 'block';
    }

    formatAnalysisText(text) {
        // Convert markdown-like formatting to HTML
        let formatted = text;

        // Headers
        formatted = formatted.replace(/## (.*)/g, '<h4 class="analysis-section-title"><i class="fas fa-chevron-right me-2"></i>$1</h4>');
        formatted = formatted.replace(/# (.*)/g, '<h3 class="analysis-main-title">$1</h3>');

        // Bold text
        formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Lists
        formatted = formatted.replace(/^\* (.*)/gm, '<li>$1</li>');
        formatted = formatted.replace(/^- (.*)/gm, '<li>$1</li>');

        // Wrap consecutive <li> elements in <ul>
        formatted = formatted.replace(/(<li>.*<\/li>)/gs, (match) => {
            return '<ul class="analysis-list">' + match + '</ul>';
        });

        // Numbers/rankings
        formatted = formatted.replace(/^(\d+)\. (.*)/gm, '<div class="analysis-point"><span class="point-number">$1</span>$2</div>');

        // Paragraphs
        formatted = formatted.replace(/\n\n/g, '</p><p>');
        formatted = '<p>' + formatted + '</p>';

        // Clean up empty paragraphs
        formatted = formatted.replace(/<p><\/p>/g, '');
        formatted = formatted.replace(/<p>\s*<\/p>/g, '');

        return formatted;
    }

    showLoading() {
        const loading = document.getElementById('analysisLoading');
        if (loading) {
            loading.style.display = 'block';
        }

        // Disable generate button
        const btn = document.getElementById('generateAnalysis');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
        }
    }

    hideLoading() {
        const loading = document.getElementById('analysisLoading');
        if (loading) {
            loading.style.display = 'none';
        }

        // Re-enable generate button.
        const btn = document.getElementById('generateAnalysis');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-magic me-2"></i>Generate AI Analysis';
        }
    }

    showResults() {
        const container = document.getElementById('analysisContainer');
        if (container) {
            container.style.display = 'block';
        }
    }

    hideResults() {
        const container = document.getElementById('analysisContainer');
        if (container) {
            container.style.display = 'none';
        }
    }

    hideGettingStarted() {
        const gettingStarted = document.getElementById('gettingStarted');
        if (gettingStarted) {
            gettingStarted.style.display = 'none';
        }
    }

    showError(message) {
        Utils.showError(`Analysis generation failed: ${message}`);
        
        // Show getting started again
        const gettingStarted = document.getElementById('gettingStarted');
        if (gettingStarted) {
            gettingStarted.style.display = 'block';
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.analysisManager = new AnalysisManager();
});