# 🌊 BLIMAS - Bolgoda Lake Information Monitoring & Analysis System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.0-purple.svg)](https://getbootstrap.com)
[![Chart.js](https://img.shields.io/badge/Chart.js-latest-orange.svg)](https://www.chartjs.org)

A comprehensive environmental monitoring system for real-time lake data visualization and analysis, featuring IoT sensor integration, AI-powered insights, and responsive web interface.

## 🚀 Features

### 📊 Real-time Monitoring
- **Live Dashboard**: Auto-refreshing sensor data every 5 minutes
- **Multi-parameter Tracking**: Air temperature, humidity, water level, and water temperature at multiple depths
- **Historical Data Visualization**: Interactive charts with day/week/month filtering
- **Mobile-responsive Interface**: Optimized for all device sizes

### 🤖 AI-Powered Analysis
- **Intelligent Insights**: Environmental trend analysis using Google Gemini API
- **Pattern Recognition**: Automated detection of environmental patterns
- **Predictive Analytics**: Data-driven environmental forecasting
- **Custom Analysis Reports**: Comprehensive environmental assessments

### 🛠️ Technical Features
- **RESTful API**: Clean endpoints for data retrieval and integration
- **IoT Integration**: LoRaWAN sensor network with ESP32-based receivers
- **Real-time Communication**: Hotspot capability for remote data collection
- **Security-first Design**: SQL injection prevention, XSS protection, input validation
- **Performance Optimized**: Gzip compression, browser caching, efficient database queries

## 🏗️ Architecture

```
BLIMAS/
├── 🌐 Frontend (PHP + Bootstrap + Chart.js)
├── 🔌 API Layer (RESTful endpoints)
├── 🗄️ Database (MySQL with optimized schemas)
├── 🛰️ IoT Layer (LoRaWAN + ESP32)
└── 🤖 AI Analysis (Google Gemini integration)
```

## 🛠️ Technology Stack

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Backend** | PHP 7.4+ | Server-side logic and API |
| **Frontend** | Bootstrap 5.3, Chart.js | Responsive UI and data visualization |
| **Database** | MySQL 5.7+ | Data storage and management |
| **IoT Hardware** | ESP32, LoRaWAN modules | Sensor data collection |
| **AI/ML** | Google Gemini API | Environmental analysis |
| **Security** | PDO, HTTPS, Input validation | Data protection |

## 📦 Installation

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher with PDO MySQL extension
- MySQL 5.7 or higher
- Modern web browser
- SSL certificate (recommended for production)

### Quick Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/keshaka/BLIMAS.git
   cd BLIMAS
   ```

2. **Database Configuration**
   ```sql
   -- Create database
   CREATE DATABASE IF NOT EXISTS blimas_db;
   USE blimas_db;
   
   -- Create sensor data table
   CREATE TABLE IF NOT EXISTS sensor_data (
       id INT AUTO_INCREMENT PRIMARY KEY,
       air_temperature DECIMAL(5,2),
       humidity DECIMAL(5,2),
       water_level DECIMAL(8,2),
       water_temp_depth1 DECIMAL(5,2),
       water_temp_depth2 DECIMAL(5,2),
       water_temp_depth3 DECIMAL(5,2),
       timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
       INDEX idx_timestamp (timestamp)
   );

   -- Create the battery_status table
   CREATE TABLE IF NOT EXISTS battery_status (
      id INT AUTO_INCREMENT PRIMARY KEY,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
      battery_percentage INT,
      rssi INT
   );
   ```

3. **Configure Database Connection**
   ```php
   // Edit config/database.php
   private $host = "localhost";
   private $db_name = "blimas_db";
   private $username = "your_username";
   private $password = "your_password";
   ```

4. **Set File Permissions**
   ```bash
   chmod 755 api/
   chmod 644 config/database.php
   chmod 644 config/gemini.php
   ```

5. **Generate Sample Data (Optional)**
   ```bash
   php sample_data.php
   ```

6. **Configure AI Analysis (Optional)**
   ```php
   // Edit config/gemini.php
   define('GEMINI_API_KEY', 'your_gemini_api_key');
   ```

## 📁 Project Structure

```
BLIMAS/
├── 📂 api/                          # RESTful API endpoints
│   ├── get_latest_data.php          # Latest sensor readings
│   ├── get_historical_data.php      # Historical data retrieval
│   ├── get_water_temp_data.php      # Water temperature data
│   └── generate_analysis.php        # AI analysis endpoint
├── 📂 assets/                       # Static assets
│   ├── 📂 css/
│   │   └── style.css               # Main stylesheet
│   └── 📂 js/
│       ├── main.js                 # Core JavaScript
│       ├── dashboard.js            # Dashboard functionality
│       └── [sensor-specific].js   # Individual sensor scripts
├── 📂 config/                       # Configuration files
│   ├── database.php                # Database configuration
│   └── gemini.php                  # AI API configuration
├── 📂 includes/                     # Common components
│   ├── header.php                  # Site header
│   └── footer.php                  # Site footer
├── 📂 sketch/                       # IoT device firmware
│   ├── 📂 receiver/                # LoRaWAN receiver code
│   └── 📂 transmitter/             # Sensor transmitter code
├── 📄 index.php                    # Main dashboard
├── 📄 [sensor-pages].php           # Individual sensor pages
├── 📄 analysis.php                 # AI analysis interface
├── 📄 sample_data.php              # Data generator
└── 📄 debug_api.php                # API testing tool
```

## 🔌 API Reference

### Latest Data
```http
GET /api/get_latest_data.php
```
Returns the most recent sensor readings.

**Response:**
```json
{
  "status": "success",
  "data": {
    "air_temperature": 28.5,
    "humidity": 75.2,
    "water_level": 1.45,
    "water_temp_depth1": 26.8,
    "water_temp_depth2": 25.9,
    "water_temp_depth3": 24.7,
    "timestamp": "2024-08-06 08:30:00"
  }
}
```

### Historical Data
```http
GET /api/get_historical_data.php?type={sensor_type}&period={time_period}
```

**Parameters:**
- `type`: `air_temperature`, `humidity`, `water_level`
- `period`: `day`, `week`, `month`

### Water Temperature Data
```http
GET /api/get_water_temp_data.php?period={time_period}
```
Returns temperature data for all three depths.

### AI Analysis
```http
GET /api/generate_analysis.php?type={analysis_type}&period={time_period}
```
Generate AI-powered environmental analysis reports.

## 🎨 Customization

### Styling
```css
/* Edit assets/css/style.css */
:root {
  --primary-color: #007bff;
  --secondary-color: #6c757d;
  --success-color: #28a745;
  /* Customize color variables */
}
```

### Data Refresh Rate
```javascript
// Edit assets/js/dashboard.js
this.updateInterval = 5 * 60 * 1000; // 5 minutes
```

### Chart Configuration
Customize visualizations in individual JavaScript files:
- `ChartDefaults` object in `main.js`
- Sensor-specific configurations in respective files

## 🛰️ IoT Integration

### Hardware Requirements
- ESP32 development board
- LoRaWAN transceiver module
- Environmental sensors (temperature, humidity, water level)
- Power supply and enclosure

### Firmware Features
- **Dual Connectivity**: LoRaWAN and WiFi capabilities
- **Hotspot Mode**: Creates access point for remote configuration
- **Data Relay**: Automatic data transmission to cloud server
- **Web Interface**: Built-in configuration portal
- **OTA Updates**: Over-the-air firmware updates

## 🔒 Security Features

- **SQL Injection Prevention**: Prepared statements with PDO
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Token-based request validation
- **Access Control**: File permission restrictions
- **HTTPS Support**: SSL/TLS encryption ready
- **Input Validation**: Comprehensive data validation

## ⚡ Performance Optimization

- **Gzip Compression**: Reduced bandwidth usage
- **Browser Caching**: Optimized static resource delivery
- **Database Indexing**: Efficient query performance
- **Lazy Loading**: Progressive content loading
- **Minified Assets**: Compressed CSS/JS files
- **CDN Ready**: External resource optimization

## 📱 Browser Support

| Browser | Version |
|---------|---------|
| Chrome | 60+ |
| Firefox | 55+ |
| Safari | 12+ |
| Edge | 79+ |

## 🔧 Development Tools

- **API Testing**: Built-in `debug_api.php` tool
- **Sample Data**: Automated data generation
- **Error Logging**: Comprehensive logging system
- **Development Mode**: Debug-friendly configurations

## 🚨 Troubleshooting

### Common Issues

**Database Connection Error**
```bash
# Check credentials in config/database.php
# Verify MySQL service status
sudo systemctl status mysql
```

**Charts Not Loading**
- Check browser console for JavaScript errors
- Verify API endpoint accessibility
- Ensure sample data exists

**Data Not Updating**
- Verify API responses in browser DevTools
- Check database connectivity
- Review error logs

**IoT Connectivity Issues**
- Verify LoRaWAN network coverage
- Check sensor power supply
- Review firmware configuration

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### Development Guidelines
- Follow PSR-4 coding standards for PHP
- Use semantic versioning for releases
- Include comprehensive tests
- Update documentation for new features

## 📋 Roadmap

- [ ] **Machine Learning Integration**: Advanced predictive modeling
- [ ] **Mobile Application**: Native iOS/Android apps
- [ ] **Multi-site Support**: Multiple monitoring locations
- [ ] **Alert System**: Real-time environmental alerts
- [ ] **Data Export**: CSV/Excel export functionality
- [ ] **User Management**: Role-based access control

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Bolgoda Lake Conservation**: Environmental monitoring initiative
- **IoT Community**: Hardware and firmware contributions
- **Open Source Libraries**: Bootstrap, Chart.js, and other dependencies
- **Google Gemini**: AI analysis capabilities

## 📞 Support

- **Documentation**: Check inline code comments
- **Issues**: GitHub issue tracker
- **Email**: [Your contact email]
- **Wiki**: Project wiki for detailed guides

## 📈 Changelog

### Version 1.1.0 (2024-08-06)
- ✨ Added AI-powered environmental analysis
- 🔧 Enhanced IoT integration with hotspot capability
- 🎨 Improved responsive design
- 🔒 Enhanced security features

### Version 1.0.0 (2024-08-03)
- 🎉 Initial release
- 📊 Real-time dashboard
- 📈 Historical data visualization
- 🔌 RESTful API endpoints
- 📱 Responsive design

---

<div align="center">


[🌊 Live Demo](https://blimas.site) • [📖 Documentation](https://github.com/keshaka/BLIMAS/wiki) • [🐛 Report Bug](https://github.com/keshaka/BLIMAS/issues)

</div>