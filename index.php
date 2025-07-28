<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLIMAS - Bolgoda Lake Monitoring System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
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
                <li><a href="analysis.php">Analysis</a></li>
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
    </style>

    <script src="assets/js/main.js"></script>
</body>
</html>