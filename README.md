# BLIMAS - Bolgoda Lake Information Monitoring & Analysis System

[![BLIMAS](http://blimas.pasgorasa.site/images/logo.png)](https://blimas.pasgorasa.site)
[![language](https://img.shields.io/badge/Language-PHP%2FMySQL%2FJS%2FArduino-blue)]()
[![GitHub release](https://img.shields.io/github/v/release/keshaka/BLIMAS)](#)
[![GitHub release date](https://img.shields.io/github/release-date/keshaka/BLIMAS)](#)
[![GitHub last commit](https://img.shields.io/github/last-commit/keshaka/BLIMAS)](#)

## 🌊 Overview

**BLIMAS** is a comprehensive real-time environmental monitoring system for Bolgoda Lake in Moratuwa, Sri Lanka. The system provides continuous monitoring of water quality parameters, weather conditions, and environmental data through an integrated web interface.

🔥 **Why BLIMAS?** — Learn more in our [presentation](https://www.canva.com/design/DAGXYRGw4qE/mx7e6SYuHaagCkh4dVNc0Q/edit?utm_content=DAGXYRGw4qE&utm_campaign=designshare&utm_medium=link2&utm_source=sharebutton) 📑

👉 **Official Website** - [blimas.pasgorasa.site](http://blimas.pasgorasa.site)

## ✨ Features

### 🏠 Homepage with Real-time Dashboard
- **Real-time sensor data** with automatic updates every 5 seconds
- **Weather information** for Katubedda, Sri Lanka
- **System status indicators** showing connection health
- **Interactive data visualization** with smooth animations
- **Mobile-responsive design** optimized for all devices

### 📊 Individual Monitoring Pages
- **Temperature Page** - Air temperature trends with interactive charts
- **Humidity Page** - Atmospheric humidity monitoring
- **Water Level Page** - Lake water level tracking
- **Water Temperature Page** - Multi-depth water temperature analysis

### 🛠 Technical Features
- **PHP backend** with MySQL database
- **Real-time AJAX updates** for live data streaming
- **Chart.js integration** for beautiful data visualization
- **OpenWeatherMap API** integration with intelligent caching
- **RESTful API endpoints** for external integrations
- **Responsive CSS** with smooth animations and transitions
- **Database caching** for optimal performance
- **Error handling** and system monitoring

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx)
- OpenWeatherMap API key (optional)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/keshaka/BLIMAS.git
   cd BLIMAS
   ```

2. **Set up the database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Configure database connection**
   ```bash
   cp config.template.php config.php
   # Edit config.php with your database credentials
   ```

4. **Configure web server** (Apache example)
   ```apache
   <VirtualHost *:80>
       ServerName your-domain.com
       DocumentRoot /path/to/BLIMAS
       # Additional configuration...
   </VirtualHost>
   ```

5. **Set up weather API** (optional)
   - Get API key from [OpenWeatherMap](https://openweathermap.org/api)
   - Update the API key in your configuration

📖 **Detailed installation guide**: [docs/INSTALLATION.md](docs/INSTALLATION.md)

## 📊 Data Collection

### Sensor Parameters
- **Air Temperature** - Environmental temperature (°C)
- **Humidity** - Atmospheric humidity (%)
- **Water Level** - Lake water level (cm)
- **Water Temperature (3 depths)** - Multi-level water temperature monitoring (°C)
- **Battery Level** - Sensor system battery status (%)

### Arduino Integration
**Pin Connections**
- DS18B20 (Water temperature sensors) → Pin D2
- DHT11 (Air temperature & humidity) → Pin D3
- Ultrasonic sensor TRIG → Pin D4
- Ultrasonic sensor ECHO → Pin D5

### Data Submission
Submit sensor data via POST request to `/upload.php`:
```bash
curl -X POST https://your-domain.com/upload.php \
  -d "water_temp1=26.5" \
  -d "water_temp2=25.8" \
  -d "water_temp3=24.9" \
  -d "humidity=75.2" \
  -d "air_temp=28.5" \
  -d "water_level=120.5" \
  -d "battery_level=85.3"
```

## 🔌 API Endpoints

### Real-time Data
```bash
# Get latest sensor readings
GET /api/data.php?action=latest

# Get current weather data
GET /api/data.php?action=weather

# Get historical data for a parameter
GET /api/data.php?action=historical&parameter=air_temperature&limit=50

# Get system status
GET /api/data.php?action=status
```

📚 **Complete API documentation**: [docs/API.md](docs/API.md)

## 🏗 Architecture

### Backend
- **PHP 7.4+** - Server-side logic and API endpoints
- **MySQL** - Primary data storage with optimized schema
- **Weather API** - OpenWeatherMap integration with caching

### Frontend
- **HTML5** - Modern semantic markup
- **CSS3** - Responsive design with animations
- **JavaScript** - Real-time updates and Chart.js integration
- **AJAX** - Asynchronous data fetching

### Database Schema
```sql
-- Main sensor data table
sensor_data (
    id, air_temperature, humidity, water_level,
    water_temp1, water_temp2, water_temp3,
    battery_level, timestamp
)

-- Weather data cache
weather_cache (
    id, location, temperature, humidity, wind_speed,
    weather_condition, expires_at, timestamp
)

-- System configuration
system_config (
    config_key, config_value, description
)
```

## 🎨 User Interface

### Design Features
- **Modern UI** with glassmorphism effects
- **Smooth animations** for data updates
- **Real-time indicators** showing system status
- **Interactive charts** with hover effects
- **Mobile-first** responsive design
- **Dark mode** support (system preference)
- **Accessibility** features for all users

### Performance
- **Real-time updates** every 5 seconds
- **Weather caching** for 10 minutes
- **Optimized queries** with proper indexing
- **Compressed assets** for faster loading
- **CDN integration** for Chart.js

## 🌐 Deployment

### Hosting Options
- **VPS/Dedicated servers** (Recommended)
- **Shared hosting** with PHP/MySQL support
- **Cloud platforms** (AWS, DigitalOcean, etc.)

### Production Checklist
- [ ] Configure SSL certificate
- [ ] Set up automated backups
- [ ] Enable error logging
- [ ] Configure monitoring
- [ ] Set up proper file permissions
- [ ] Enable GZIP compression
- [ ] Configure caching headers

## 🔧 Monitoring & Maintenance

### System Health
- **Connection indicators** show real-time status
- **Error logging** for troubleshooting
- **Performance monitoring** via API endpoints
- **Automated cache cleanup** for weather data

### Backup Strategy
```bash
# Database backup
mysqldump -u username -p blimas_db > backup_$(date +%Y%m%d).sql

# Full system backup
tar -czf blimas_backup_$(date +%Y%m%d).tar.gz /path/to/BLIMAS
```

## 🤝 Contributing

We welcome contributions! Here's how you can help:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Commit your changes** (`git commit -m 'Add amazing feature'`)
4. **Push to the branch** (`git push origin feature/amazing-feature`)
5. **Open a Pull Request**

### Development Setup
```bash
# Clone and set up development environment
git clone https://github.com/keshaka/BLIMAS.git
cd BLIMAS
cp config.template.php config.php
# Configure your local database
mysql -u root -p < database/schema.sql
```

## 👥 Contributors

- [Kevin](https://github.com/Kevin200307) - Lead Developer
- [LP-Ishadi](https://github.com/LP-Ishadi) - Frontend Developer
- [keshaka](https://github.com/keshaka) - Project Maintainer

## 📄 License

This project is open source. Please check the repository for license details.

## 🆘 Support

Need help? Here are your options:

1. 📖 **Check the documentation** in the `docs/` folder
2. 🐛 **Report issues** on GitHub
3. 💬 **Join discussions** in the repository
4. 📧 **Contact the team** through GitHub

## 🌟 Acknowledgments

- **Themezy** - Original UI template inspiration
- **OpenWeatherMap** - Weather data API
- **Chart.js** - Data visualization library
- **Font Awesome** - Icon library

---

**⭐ Star this repository if you find it useful!**

[Back to top](#blimas---bolgoda-lake-information-monitoring--analysis-system)
