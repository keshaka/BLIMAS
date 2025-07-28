<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLIMAS - Bolgoda Lake Monitoring System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">BLIMAS</a>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="temperature.php">Temperature</a></li>
                <li><a href="humidity.php">Humidity</a></li>
                <li><a href="water-level.php">Water Level</a></li>
                <li><a href="water-temperature.php">Water Temperature</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <h1 class="page-title">Bolgoda Lake Monitoring System</h1>
        
        <div class="dashboard-grid">
            <!-- Air Temperature Card -->
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">🌡️</div>
                    <div class="card-title">Air Temperature</div>
                </div>
                <div class="card-value" id="air-temperature">--.-</div>
                <div class="card-unit">°C</div>
                <div class="status-indicator">
                    <div class="status-dot status-normal" id="temp-status"></div>
                    <span>Normal Range</span>
                </div>
            </div>

            <!-- Humidity Card -->
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">💧</div>
                    <div class="card-title">Humidity</div>
                </div>
                <div class="card-value" id="humidity">--.-</div>
                <div class="card-unit">%</div>
                <div class="status-indicator">
                    <div class="status-dot status-normal" id="humidity-status"></div>
                    <span>Normal Range</span>
                </div>
            </div>

            <!-- Water Level Card -->
            <div class="data-card">
                <div class="card-header">
                    <div class="card-icon">🌊</div>
                    <div class="card-title">Water Level</div>
                </div>
                <div class="card-value" id="water-level">--.-</div>
                <div class="card-unit">m</div>
                <div class="status-indicator">
                    <div class="status-dot status-normal" id="water-level-status"></div>
                    <span>Normal Range</span>
                </div>
            </div>

            <!-- Weather Widget -->
            <div class="weather-widget">
                <div class="weather-icon" id="weather-icon">🌤️</div>
                <div class="weather-temp" id="weather-temp">--</div>
                <div class="weather-desc" id="weather-desc">Loading...</div>
                <div class="weather-details">
                    <div class="weather-detail">
                        <div>Humidity</div>
                        <div id="weather-humidity">--%</div>
                    </div>
                    <div class="weather-detail">
                        <div>Pressure</div>
                        <div id="weather-pressure">-- hPa</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Water Temperature Section -->
        <div class="water-temp-grid">
            <div class="depth-card">
                <div class="depth-label">Surface (Depth 1)</div>
                <div class="depth-value" id="water-temp-1">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
            <div class="depth-card">
                <div class="depth-label">Middle (Depth 2)</div>
                <div class="depth-value" id="water-temp-2">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
            <div class="depth-card">
                <div class="depth-label">Bottom (Depth 3)</div>
                <div class="depth-value" id="water-temp-3">--.-</div>
                <div class="depth-unit">°C</div>
            </div>
        </div>

        <!-- AI Analysis Dashboard Section -->
        <div class="ai-analysis-section">
            <h2 class="section-title">🤖 AI Analysis & Predictions</h2>
            
            <div class="analysis-tabs">
                <button class="tab-button active" data-tab="trends">📈 Trends</button>
                <button class="tab-button" data-tab="predictions">🔮 Predictions</button>
                <button class="tab-button" data-tab="anomalies">⚠️ Anomalies</button>
                <button class="tab-button" data-tab="summary">📊 Summary</button>
            </div>

            <div class="analysis-content">
                <!-- Trends Tab -->
                <div class="tab-content active" id="trends-tab">
                    <div class="analysis-grid">
                        <div class="analysis-card">
                            <h3>🌡️ Temperature Trends</h3>
                            <div class="trend-chart">
                                <canvas id="temperature-trend-chart"></canvas>
                            </div>
                            <div class="ai-insight" id="temperature-trend-insight">
                                <div class="loading-spinner">Loading AI analysis...</div>
                            </div>
                        </div>
                        
                        <div class="analysis-card">
                            <h3>💧 Humidity Patterns</h3>
                            <div class="trend-chart">
                                <canvas id="humidity-trend-chart"></canvas>
                            </div>
                            <div class="ai-insight" id="humidity-trend-insight">
                                <div class="loading-spinner">Loading AI analysis...</div>
                            </div>
                        </div>
                        
                        <div class="analysis-card full-width">
                            <h3>🌊 Water Level & Temperature Analysis</h3>
                            <div class="trend-chart">
                                <canvas id="water-analysis-chart"></canvas>
                            </div>
                            <div class="ai-insight" id="water-trend-insight">
                                <div class="loading-spinner">Loading AI analysis...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Predictions Tab -->
                <div class="tab-content" id="predictions-tab">
                    <div class="predictions-grid">
                        <div class="prediction-card">
                            <h3>📅 24-Hour Forecast</h3>
                            <div class="prediction-items" id="prediction-24h">
                                <div class="loading-spinner">Loading predictions...</div>
                            </div>
                        </div>
                        
                        <div class="prediction-card">
                            <h3>📈 Prediction Chart</h3>
                            <div class="prediction-chart">
                                <canvas id="prediction-chart"></canvas>
                            </div>
                        </div>
                        
                        <div class="prediction-card full-width">
                            <h3>🎯 AI Predictions Summary</h3>
                            <div class="ai-insight" id="prediction-insight">
                                <div class="loading-spinner">Loading AI predictions...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Anomalies Tab -->
                <div class="tab-content" id="anomalies-tab">
                    <div class="anomalies-grid">
                        <div class="anomaly-card">
                            <h3>🚨 Real-time Alerts</h3>
                            <div class="anomaly-alerts" id="anomaly-alerts">
                                <div class="loading-spinner">Checking for anomalies...</div>
                            </div>
                        </div>
                        
                        <div class="anomaly-card">
                            <h3>📉 Anomaly Detection Chart</h3>
                            <div class="anomaly-chart">
                                <canvas id="anomaly-chart"></canvas>
                            </div>
                        </div>
                        
                        <div class="anomaly-card full-width">
                            <h3>🔍 AI Anomaly Analysis</h3>
                            <div class="ai-insight" id="anomaly-insight">
                                <div class="loading-spinner">Loading anomaly analysis...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Tab -->
                <div class="tab-content" id="summary-tab">
                    <div class="summary-grid">
                        <div class="summary-card">
                            <h3>📋 Executive Summary</h3>
                            <div class="summary-content" id="executive-summary">
                                <div class="loading-spinner">Generating summary...</div>
                            </div>
                        </div>
                        
                        <div class="summary-card">
                            <h3>📊 Key Metrics</h3>
                            <div class="metrics-display" id="key-metrics">
                                <div class="loading-spinner">Loading metrics...</div>
                            </div>
                        </div>
                        
                        <div class="summary-card full-width">
                            <h3>💡 AI Recommendations</h3>
                            <div class="ai-insight" id="summary-recommendations">
                                <div class="loading-spinner">Loading recommendations...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>BLIMAS</h3>
                <p>Monitoring Bolgoda Lake for environmental protection and sustainable development.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook">📘</a>
                    <a href="#" aria-label="Twitter">🐦</a>
                    <a href="#" aria-label="Instagram">📷</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="temperature.php">Temperature</a></li>
                    <li><a href="humidity.php">Humidity</a></li>
                    <li><a href="water-level.php">Water Level</a></li>
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
                <div class="contact-item">
                    <span>📍</span>
                    <span>Bolgoda Lake, Sri Lanka</span>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> BLIMAS. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Support</a>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ecf0f1;
            margin-top: 60px;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2ecc71, #f39c12, #e74c3c);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 20px 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
        }

        .footer-section h3 {
            color: #3498db;
            font-size: 1.8rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .footer-section h4 {
            color: #ecf0f1;
            font-size: 1.2rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            font-weight: 600;
        }

        .footer-section p {
            line-height: 1.6;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .footer-social {
            display: flex;
            gap: 15px;
        }

        .footer-social a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(52, 152, 219, 0.2);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .footer-social a:hover {
            background: #3498db;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 12px;
        }

        .footer-section ul li a {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 20px;
        }

        .footer-section ul li a::before {
            content: '▶';
            position: absolute;
            left: 0;
            color: #3498db;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #3498db;
            padding-left: 25px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            gap: 12px;
        }

        .contact-item span:first-child {
            font-size: 1.2rem;
            width: 20px;
        }

        .contact-item a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-item a:hover {
            color: #3498db;
        }

        .footer-bottom {
            background: rgba(0, 0, 0, 0.3);
            padding: 25px 20px;
            border-top: 1px solid rgba(236, 240, 241, 0.1);
        }

        .footer-bottom-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-bottom p {
            margin: 0;
            color: #95a5a6;
        }

        .footer-links {
            display: flex;
            gap: 25px;
        }

        .footer-links a {
            color: #95a5a6;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: #3498db;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-links {
                justify-content: center;
            }
        }

        /* AI Analysis Section Styles */
        .ai-analysis-section {
            margin-top: 40px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .analysis-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .tab-button {
            background: rgba(102, 126, 234, 0.1);
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            color: #667eea;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .tab-button:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .tab-button.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .analysis-grid, .predictions-grid, .anomalies-grid, .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }

        .analysis-card, .prediction-card, .anomaly-card, .summary-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .analysis-card:hover, .prediction-card:hover, .anomaly-card:hover, .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .analysis-card.full-width, .prediction-card.full-width, .anomaly-card.full-width, .summary-card.full-width {
            grid-column: 1 / -1;
        }

        .analysis-card h3, .prediction-card h3, .anomaly-card h3, .summary-card h3 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            font-weight: 600;
        }

        .trend-chart, .prediction-chart, .anomaly-chart {
            height: 250px;
            margin-bottom: 15px;
            background: rgba(247, 250, 252, 0.5);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ai-insight {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 10px;
            padding: 15px;
            border-left: 4px solid #667eea;
            font-size: 14px;
            line-height: 1.6;
        }

        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-weight: 500;
        }

        .loading-spinner::before {
            content: '';
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            margin-right: 10px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .prediction-items {
            display: grid;
            gap: 10px;
        }

        .prediction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }

        .prediction-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .prediction-value {
            color: #667eea;
            font-weight: 500;
        }

        .confidence-badge {
            background: #10b981;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 8px;
        }

        .confidence-badge.medium {
            background: #f59e0b;
        }

        .confidence-badge.low {
            background: #ef4444;
        }

        .anomaly-alerts {
            max-height: 300px;
            overflow-y: auto;
        }

        .anomaly-alert {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }

        .anomaly-alert.medium {
            border-left-color: #f59e0b;
            background: rgba(245, 158, 11, 0.05);
        }

        .anomaly-alert.low {
            border-left-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }

        .anomaly-metric {
            font-weight: 600;
            color: #2c3e50;
        }

        .anomaly-value {
            color: #ef4444;
            font-weight: 500;
        }

        .anomaly-time {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }

        .summary-content, .metrics-display {
            line-height: 1.6;
        }

        .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }

        .metric-label {
            color: #2c3e50;
            font-weight: 500;
        }

        .metric-value {
            color: #667eea;
            font-weight: 600;
        }

        .status-excellent {
            color: #10b981;
            font-weight: 600;
        }

        .status-good {
            color: #f59e0b;
            font-weight: 600;
        }

        .status-warning {
            color: #ef4444;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .analysis-grid, .predictions-grid, .anomalies-grid, .summary-grid {
                grid-template-columns: 1fr;
            }
            
            .analysis-tabs {
                justify-content: center;
            }
            
            .tab-button {
                padding: 10px 16px;
                font-size: 12px;
            }
            
            .trend-chart, .prediction-chart, .anomaly-chart {
                height: 200px;
            }
        }
    </style>

    <script src="assets/js/main.js"></script>
</body>
</html>