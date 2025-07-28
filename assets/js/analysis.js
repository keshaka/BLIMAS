// Analysis Page JavaScript
class AnalysisManager {
    constructor() {
        this.init();
        this.predictionsChart = null;
    }

    init() {
        this.bindEvents();
        this.loadInitialAnalysis();
    }

    bindEvents() {
        const generateBtn = document.getElementById('generate-analysis');
        const retryBtn = document.getElementById('retry-analysis');
        const exportPdfBtn = document.getElementById('export-pdf');
        const exportJsonBtn = document.getElementById('export-json');
        const printBtn = document.getElementById('print-report');

        generateBtn.addEventListener('click', () => this.generateAnalysis());
        retryBtn.addEventListener('click', () => this.generateAnalysis());
        exportPdfBtn.addEventListener('click', () => this.exportPDF());
        exportJsonBtn.addEventListener('click', () => this.exportJSON());
        printBtn.addEventListener('click', () => this.printReport());
    }

    async loadInitialAnalysis() {
        // Load analysis on page load with default settings
        await this.generateAnalysis();
    }

    async generateAnalysis() {
        const analysisType = document.getElementById('analysis-type').value;
        const timeRange = document.getElementById('time-range').value;

        this.showLoading();
        this.hideAllSections();

        try {
            const response = await fetch(`/api/get_analysis_demo.php?type=${analysisType}&hours=${timeRange}`);
            const result = await response.json();

            if (result.status === 'success') {
                this.displayAnalysisResults(result.data);
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Analysis error:', error);
            this.showError('Failed to generate analysis. Please check your connection and try again.');
        }
    }

    displayAnalysisResults(data) {
        this.hideLoading();
        
        // Display AI Analysis
        if (data.ai_analysis && !data.ai_analysis.error) {
            this.displayAIAnalysis(data.ai_analysis.analysis);
        } else if (data.ai_analysis && data.ai_analysis.error) {
            this.displayAIAnalysisError(data.ai_analysis.message);
        }

        // Display Statistics
        if (data.statistics) {
            this.displayStatistics(data.statistics);
        }

        // Display Predictions
        if (data.predictions) {
            this.displayPredictions(data.predictions);
        }

        // Display Anomalies
        if (data.anomalies) {
            this.displayAnomalies(data.anomalies);
        }

        // Show export section
        this.showSection('export-section');
    }

    displayAIAnalysis(analysis) {
        const content = document.getElementById('ai-analysis-content');
        
        // Format the AI analysis text with proper HTML structure
        const formattedAnalysis = this.formatAIText(analysis);
        content.innerHTML = formattedAnalysis;
        
        this.showSection('ai-analysis-section');
    }

    displayAIAnalysisError(message) {
        const content = document.getElementById('ai-analysis-content');
        content.innerHTML = `
            <div class="ai-error">
                <h3>⚠️ AI Analysis Unavailable</h3>
                <p>${message}</p>
                <p><strong>Note:</strong> Statistical analysis and predictions are still available below.</p>
            </div>
        `;
        this.showSection('ai-analysis-section');
    }

    formatAIText(text) {
        // Convert plain text to formatted HTML
        let formatted = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>');

        // Wrap in paragraph tags
        formatted = '<p>' + formatted + '</p>';

        // Convert headers (lines ending with :)
        formatted = formatted.replace(/<p>([^<]+:)<\/p>/g, '<h3>$1</h3>');
        
        // Convert bullet points
        formatted = formatted.replace(/<p>[-•]\s*(.*?)<\/p>/g, '<li>$1</li>');
        
        // Wrap consecutive list items in ul tags
        formatted = formatted.replace(/(<li>.*?<\/li>)+/gs, '<ul>$&</ul>');

        return formatted;
    }

    displayStatistics(statistics) {
        const grid = document.getElementById('stats-grid');
        grid.innerHTML = '';

        Object.entries(statistics).forEach(([field, stats]) => {
            const card = this.createStatCard(stats);
            grid.appendChild(card);
        });

        this.showSection('statistics-section');
    }

    createStatCard(stats) {
        const card = document.createElement('div');
        card.className = 'stat-card';

        const trendClass = `trend-${stats.trend}`;
        
        card.innerHTML = `
            <div class="stat-header">
                <div class="stat-title">${stats.label}</div>
                <div class="trend-indicator ${trendClass}">${stats.trend}</div>
            </div>
            <div class="stat-values">
                <div class="stat-item">
                    <div class="stat-label">Average</div>
                    <div class="stat-value">${stats.average}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Range</div>
                    <div class="stat-value">${stats.range}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Minimum</div>
                    <div class="stat-value">${stats.minimum}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Maximum</div>
                    <div class="stat-value">${stats.maximum}</div>
                </div>
            </div>
        `;

        return card;
    }

    displayPredictions(predictions) {
        if (predictions.error) {
            const summary = document.getElementById('predictions-summary');
            summary.innerHTML = `
                <div class="prediction-error">
                    <h3>⚠️ Predictions Unavailable</h3>
                    <p>${predictions.error}</p>
                </div>
            `;
            this.showSection('predictions-section');
            return;
        }

        // Check if Chart.js is available, if not, show predictions without charts
        const chartContainer = document.querySelector('.chart-container');
        if (typeof Chart === 'undefined') {
            chartContainer.innerHTML = `
                <div class="chart-fallback">
                    <h3>📈 Prediction Charts</h3>
                    <p>Chart.js library is not available. Predictions are shown in the summary panel.</p>
                    <div class="prediction-note">
                        <strong>Note:</strong> In a production environment, ensure Chart.js is loaded for visual predictions.
                    </div>
                </div>
            `;
        } else {
            // Create predictions chart if Chart.js is available
            this.createPredictionsChart(predictions);
        }
        
        // Display predictions summary
        const summary = document.getElementById('predictions-summary');
        summary.innerHTML = '';

        Object.entries(predictions).forEach(([field, prediction]) => {
            const item = document.createElement('div');
            item.className = 'prediction-item';
            
            const fieldName = this.getFieldDisplayName(field);
            
            item.innerHTML = `
                <div class="prediction-header">
                    <div class="prediction-title">${fieldName}</div>
                    <div class="confidence-badge">${prediction.confidence}% confidence</div>
                </div>
                <div class="prediction-trend">Trend: <strong>${prediction.trend}</strong></div>
                <div class="prediction-values">
                    <strong>Next 6 hours:</strong><br>
                    ${prediction.future_values.map((v, i) => `Hour ${v.hour}: ${v.predicted_value}`).join('<br>')}
                </div>
            `;
            
            summary.appendChild(item);
        });

        this.showSection('predictions-section');
    }

    createPredictionsChart(predictions) {
        const ctx = document.getElementById('predictions-chart').getContext('2d');
        
        if (this.predictionsChart) {
            this.predictionsChart.destroy();
        }

        const datasets = [];
        const colors = ['#667eea', '#f093fb', '#4facfe'];
        let colorIndex = 0;

        Object.entries(predictions).forEach(([field, prediction]) => {
            if (prediction.future_values) {
                const fieldName = this.getFieldDisplayName(field);
                datasets.push({
                    label: fieldName,
                    data: prediction.future_values.map(v => ({
                        x: v.hour,
                        y: v.predicted_value
                    })),
                    borderColor: colors[colorIndex % colors.length],
                    backgroundColor: colors[colorIndex % colors.length] + '20',
                    tension: 0.4,
                    fill: false
                });
                colorIndex++;
            }
        });

        this.predictionsChart = new Chart(ctx, {
            type: 'line',
            data: { datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Future Predictions (Next 6 Hours)'
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        type: 'linear',
                        title: {
                            display: true,
                            text: 'Hours from now'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Predicted Value'
                        }
                    }
                }
            }
        });
    }

    displayAnomalies(anomalies) {
        const content = document.getElementById('anomalies-content');
        content.innerHTML = '';

        if (Object.keys(anomalies).length === 0) {
            content.innerHTML = `
                <div class="no-anomalies">
                    <h3>✅ No Anomalies Detected</h3>
                    <p>All sensor readings are within normal ranges for the selected time period.</p>
                </div>
            `;
        } else {
            Object.entries(anomalies).forEach(([field, fieldAnomalies]) => {
                const card = this.createAnomalyCard(field, fieldAnomalies);
                content.appendChild(card);
            });
        }

        this.showSection('anomalies-section');
    }

    createAnomalyCard(field, anomalies) {
        const card = document.createElement('div');
        card.className = 'anomaly-card';

        const fieldName = this.getFieldDisplayName(field);
        const highSeverityCount = anomalies.filter(a => a.severity === 'high').length;
        const severity = highSeverityCount > 0 ? 'high' : 'medium';

        card.innerHTML = `
            <div class="anomaly-header">
                <div class="anomaly-type">${fieldName}</div>
                <div class="severity-badge severity-${severity}">${severity} severity</div>
            </div>
            <div class="anomaly-list">
                ${anomalies.map(anomaly => `
                    <div class="anomaly-item">
                        <div class="anomaly-time">${new Date(anomaly.timestamp).toLocaleString()}</div>
                        <div class="anomaly-value">${anomaly.value}</div>
                    </div>
                `).join('')}
            </div>
        `;

        return card;
    }

    getFieldDisplayName(field) {
        const fieldNames = {
            'air_temperature': 'Air Temperature',
            'humidity': 'Humidity',
            'water_level': 'Water Level',
            'water_temp_depth1': 'Water Temp (Surface)',
            'water_temp_depth2': 'Water Temp (Middle)',
            'water_temp_depth3': 'Water Temp (Bottom)'
        };
        return fieldNames[field] || field;
    }

    showLoading() {
        document.getElementById('loading-state').style.display = 'block';
    }

    hideLoading() {
        document.getElementById('loading-state').style.display = 'none';
    }

    showError(message) {
        this.hideLoading();
        document.getElementById('error-message').textContent = message;
        document.getElementById('error-state').style.display = 'block';
    }

    hideAllSections() {
        const sections = [
            'ai-analysis-section',
            'statistics-section', 
            'predictions-section',
            'anomalies-section',
            'export-section',
            'error-state'
        ];
        
        sections.forEach(sectionId => {
            document.getElementById(sectionId).style.display = 'none';
        });
    }

    showSection(sectionId) {
        document.getElementById(sectionId).style.display = 'block';
    }

    exportPDF() {
        // Simple implementation using browser's print functionality
        window.print();
    }

    exportJSON() {
        // Export the current analysis data as JSON
        const analysisData = {
            timestamp: new Date().toISOString(),
            analysis_type: document.getElementById('analysis-type').value,
            time_range: document.getElementById('time-range').value,
            // Add more data as needed
        };

        const blob = new Blob([JSON.stringify(analysisData, null, 2)], {
            type: 'application/json'
        });
        
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `blimas-analysis-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    printReport() {
        // Hide non-essential elements for printing
        const controlsEl = document.querySelector('.analysis-controls');
        const exportEl = document.querySelector('.export-section');
        
        if (controlsEl) controlsEl.style.display = 'none';
        if (exportEl) exportEl.style.display = 'none';
        
        window.print();
        
        // Restore elements after printing
        setTimeout(() => {
            if (controlsEl) controlsEl.style.display = '';
            if (exportEl) exportEl.style.display = '';
        }, 1000);
    }
}

// Initialize the analysis manager when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new AnalysisManager();
});