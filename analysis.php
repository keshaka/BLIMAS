<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analysis & Predictions - BLIMAS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/analysis.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">BLIMAS</a>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="temperature.php">Temperature</a></li>
                <li><a href="humidity.php">Humidity</a></li>
                <li><a href="water-level.php">Water Level</a></li>
                <li><a href="water-temperature.php">Water Temperature</a></li>
                <li><a href="analysis.php" class="active">Analysis</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content page-enter">
        <h1 class="page-title">AI-Powered Analysis & Predictions</h1>
        
        <!-- Analysis Controls -->
        <div class="analysis-controls">
            <div class="control-group">
                <label for="analysis-type">Analysis Type:</label>
                <select id="analysis-type" class="control-select">
                    <option value="general">General Analysis</option>
                    <option value="trends">Trend Analysis</option>
                    <option value="predictions">Predictions</option>
                    <option value="anomalies">Anomaly Detection</option>
                    <option value="insights">Environmental Insights</option>
                </select>
            </div>
            <div class="control-group">
                <label for="time-range">Data Period:</label>
                <select id="time-range" class="control-select">
                    <option value="6">Last 6 Hours</option>
                    <option value="24" selected>Last 24 Hours</option>
                    <option value="72">Last 3 Days</option>
                    <option value="168">Last 7 Days</option>
                </select>
            </div>
            <button id="generate-analysis" class="btn-primary">Generate Analysis</button>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="loading-state" style="display: none;">
            <div class="loading-spinner"></div>
            <p>Analyzing data with AI...</p>
        </div>

        <!-- AI Analysis Section -->
        <div id="ai-analysis-section" class="analysis-section" style="display: none;">
            <h2 class="section-title">🤖 AI Analysis Results</h2>
            <div class="ai-analysis-card">
                <div id="ai-analysis-content" class="ai-content">
                    <!-- AI analysis will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div id="statistics-section" class="analysis-section" style="display: none;">
            <h2 class="section-title">📊 Statistical Overview</h2>
            <div class="stats-grid" id="stats-grid">
                <!-- Statistics cards will be generated here -->
            </div>
        </div>

        <!-- Predictions Section -->
        <div id="predictions-section" class="analysis-section" style="display: none;">
            <h2 class="section-title">🔮 Future Predictions</h2>
            <div class="predictions-container">
                <div class="chart-container">
                    <canvas id="predictions-chart"></canvas>
                </div>
                <div id="predictions-summary" class="predictions-summary">
                    <!-- Predictions summary will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Anomalies Section -->
        <div id="anomalies-section" class="analysis-section" style="display: none;">
            <h2 class="section-title">⚠️ Anomaly Detection</h2>
            <div id="anomalies-content" class="anomalies-content">
                <!-- Anomalies will be displayed here -->
            </div>
        </div>

        <!-- Export Controls -->
        <div id="export-section" class="export-section" style="display: none;">
            <h3>Export Analysis</h3>
            <div class="export-buttons">
                <button id="export-pdf" class="btn-secondary">📄 Export as PDF</button>
                <button id="export-json" class="btn-secondary">📋 Export Data (JSON)</button>
                <button id="print-report" class="btn-secondary">🖨️ Print Report</button>
            </div>
        </div>

        <!-- Error State -->
        <div id="error-state" class="error-state" style="display: none;">
            <div class="error-icon">❌</div>
            <h3>Analysis Error</h3>
            <p id="error-message">An error occurred while generating the analysis.</p>
            <button id="retry-analysis" class="btn-primary">Retry Analysis</button>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>BLIMAS</h3>
                <p>Advanced AI-powered monitoring and analysis for Bolgoda Lake environmental protection.</p>
            </div>
            
            <div class="footer-section">
                <h4>Analysis Features</h4>
                <ul>
                    <li><a href="#ai-analysis">AI-Powered Insights</a></li>
                    <li><a href="#predictions">Predictive Modeling</a></li>
                    <li><a href="#anomalies">Anomaly Detection</a></li>
                    <li><a href="#statistics">Statistical Analysis</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contact Info</h4>
                <div class="contact-item">
                    <span>📧</span>
                    <a href="mailto:info@blimas.com">info@blimas.com</a>
                </div>
                <div class="contact-item">
                    <span>📞</span>
                    <span>+94 11 234 5678</span>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> BLIMAS. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">API Documentation</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/analysis.js"></script>
</body>
</html>