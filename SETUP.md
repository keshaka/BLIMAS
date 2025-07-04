# BLIMAS - Bolgoda Lake Information Monitoring & Analysis System

A comprehensive web-based monitoring system for environmental data from Bolgoda Lake in Sri Lanka.

![BLIMAS Dashboard](https://github.com/user-attachments/assets/d307a74e-6b3c-4d2e-adf1-ea7f3eba5368)

## 🌟 Features

### Real-time Dashboard
- Live sensor data display with beautiful gradient cards
- Air temperature, humidity, water level, and battery monitoring
- Multi-depth water temperature analysis
- Integrated weather data for Katubedda, Sri Lanka
- Auto-refresh functionality every 5 seconds
- Responsive design with smooth animations

### Individual Data Analysis Pages
- **Temperature Page**: Air temperature trends with interactive charts
- **Humidity Page**: Humidity analysis with status indicators
- **Water Level Page**: Visual water level monitoring with gradient indicators
- **Water Temperature Page**: Multi-depth temperature comparison

### Advanced Features
- **Data Export**: JSON, CSV, XML formats with filtering options
- **Alert System**: Automated monitoring with configurable thresholds
- **Admin Panel**: System management and monitoring tools
- **Weather Integration**: Live weather data from external API
- **Interactive Charts**: Beautiful visualizations using Chart.js

## 🗄️ Database Schema

The system uses MySQL with the following main tables:

- `sensor_data`: Main sensor readings (temperature, humidity, water level, battery)
- `weather_data`: External weather API data cache
- `alerts`: Alert management system
- `system_config`: Configuration management

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4+ with PDO extension
- MySQL 5.7+ or MariaDB
- Web server (Apache/Nginx)
- Composer (optional, for additional packages)

### Step 1: Database Setup
```sql
-- Import the database schema
source sql/database-setup.sql;

-- Update database credentials in config/database.php
```

### Step 2: Configuration
```php
// Update config/config.php
define('WEATHER_API_KEY', 'your_api_key_here');
define('SITE_URL', 'http://your-domain.com');

// Update config/database.php with your database credentials
```

### Step 3: File Permissions
```bash
# Ensure web server can read files
chmod -R 755 /path/to/blimas/
chown -R www-data:www-data /path/to/blimas/
```

### Step 4: Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^?]*)$ index.php [NC,L,QSA]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 📊 API Endpoints

### Sensor Data
```
GET /api/get-data.php
Returns latest sensor readings in JSON format
```

### Weather Data
```
GET /api/weather.php
Returns current weather data for Katubedda, Sri Lanka
```

### Data Export
```
GET /api/export-data.php?format={json|csv|xml}&type={all|temperature|humidity|water_level|water_temperature}&limit={number}
Export sensor data in various formats
```

### Alert System
```
GET /api/alerts.php?action={check|active|acknowledge}
Manage alert system
```

## 🎛️ Admin Panel

Access the admin panel at `/pages/admin.php`

**Default credentials:**
- Password: `blimas_admin_2025`

### Admin Features
- System statistics and monitoring
- Data export management
- Alert management
- API testing tools
- Recent data overview

## 🔧 Hardware Integration

### Arduino Sensor Setup
The system expects data from Arduino sensors:

- **DS18B20**: Water temperature sensors (3 units at different depths)
- **DHT11/DHT22**: Air temperature and humidity
- **HC-SR04**: Ultrasonic sensor for water level
- **Battery monitoring**: System power level

### Data Upload Format
Sensors should POST data to `/upload.php` with:
```
water_temp1, water_temp2, water_temp3, humidity, air_temp, water_level, battery_level
```

## 🎨 Customization

### Themes and Colors
Update `assets/css/dashboard.css` to modify:
- Card gradient colors
- Animation styles
- Responsive breakpoints

### Alert Thresholds
Modify `config/config.php`:
```php
define('TEMP_HIGH_THRESHOLD', 35);
define('HUMIDITY_HIGH_THRESHOLD', 90);
define('WATER_LEVEL_LOW_THRESHOLD', 50);
```

## 📱 Mobile Responsiveness

The system is fully responsive and optimized for:
- Desktop computers
- Tablets
- Mobile phones
- Touch interfaces

## 🔐 Security Features

- Input validation and sanitization
- SQL injection prevention with prepared statements
- XSS protection
- Session management for admin panel
- Rate limiting for API endpoints

## 🌍 Weather API Integration

The system integrates with WeatherAPI.com for local weather data:

1. Sign up for a free API key at [weatherapi.com](https://weatherapi.com)
2. Update `WEATHER_API_KEY` in `config/config.php`
3. Weather data updates every 5 minutes

## 📈 Performance Optimization

- **Caching**: Weather data cached for 5 minutes
- **Database indexing**: Optimized queries with proper indexes
- **Asset optimization**: Minified CSS and JS (can be implemented)
- **Image optimization**: Compressed images and proper formats

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify network connectivity

2. **No Sensor Data**
   - Check Arduino sensor connections
   - Verify data upload endpoint (`/upload.php`)
   - Check database table permissions

3. **Weather Data Not Loading**
   - Verify API key in `config/config.php`
   - Check internet connectivity
   - Review API usage limits

## 📊 Data Analysis

### Export Options
- **JSON**: For API integration and web applications
- **CSV**: For Excel and data analysis tools
- **XML**: For enterprise system integration

### Visualization Tools
The system provides:
- Real-time line charts
- Historical trend analysis
- Comparative multi-depth temperature charts
- Statistical summaries

## 🔄 Backup & Maintenance

### Database Backup
```bash
# Daily backup
mysqldump -u username -p blimas > backup_$(date +%Y%m%d).sql

# Automated cleanup of old data
mysql -u username -p blimas -e "CALL CleanOldData(365);"
```

### Log Management
- Monitor PHP error logs
- Check web server access logs
- Review alert system logs

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Authors

- **BLIMAS Team** - Initial work and development
- **Contributors** - See the contributors list

## 🙏 Acknowledgments

- Weather data provided by WeatherAPI.com
- Chart visualization by Chart.js
- Font icons by Font Awesome
- CSS framework inspirations from modern design patterns

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Check the troubleshooting section
- Review the API documentation

---

**BLIMAS** - Monitoring Bolgoda Lake for a sustainable future 🌊