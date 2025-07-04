# BLIMAS Installation & Setup Guide

## Overview

BLIMAS (Bolgoda Lake Information Monitoring & Analysis System) is a comprehensive web-based monitoring system for collecting and displaying environmental data from Bolgoda Lake in Moratuwa, Sri Lanka.

## Features

### Core Features
- **Real-time data display** showing air temperature, humidity, water level, and water temperature from 3 depth levels
- **Weather information** for Katubedda, Sri Lanka using OpenWeatherMap API
- **Separate visualization pages** for each monitoring parameter with interactive charts
- **Responsive design** optimized for mobile and desktop
- **Real-time data updates** using AJAX every 5 seconds
- **Chart visualization** using Chart.js
- **Database caching** for improved performance

### Technical Features
- PHP backend with MySQL database
- Modern CSS with smooth animations
- Real-time status indicators
- Weather widget integration
- Mobile-responsive navigation
- Loading animations and transitions
- Optimized for Ubuntu VPS hosting

## Requirements

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache or Nginx
- **PHP Extensions**:
  - mysqli
  - pdo
  - json
  - curl (for weather API)

### Optional Requirements
- **OpenWeatherMap API Key** (for weather data)
- **SSL Certificate** (recommended for production)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/keshaka/BLIMAS.git
cd BLIMAS
```

### 2. Database Setup

#### Create Database
```sql
mysql -u root -p < database/schema.sql
```

#### Or manually create:
```sql
CREATE DATABASE blimas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import the schema:
```sql
mysql -u username -p blimas_db < database/schema.sql
```

### 3. Configure Database Connection

Edit `includes/database.php` and update the configuration:

```php
private static $config = [
    'host' => 'localhost',        // Your database host
    'username' => 'your_username', // Your database username
    'password' => 'your_password', // Your database password
    'database' => 'blimas_db',
    'charset' => 'utf8mb4',
    'port' => 3306
];
```

### 4. Configure Weather API (Optional)

1. Get a free API key from [OpenWeatherMap](https://openweathermap.org/api)
2. Update the configuration in your database:

```sql
UPDATE system_config 
SET config_value = 'your_api_key_here' 
WHERE config_key = 'weather_api_key';
```

Or update directly in the index.html file:
```javascript
const apiKey = 'your_api_key_here';
```

### 5. Set Up Web Server

#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/BLIMAS
    
    <Directory /path/to/BLIMAS>
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/blimas_error.log
    CustomLog ${APACHE_LOG_DIR}/blimas_access.log combined
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/BLIMAS;
    index index.html index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 6. Set File Permissions

```bash
sudo chown -R www-data:www-data /path/to/BLIMAS
sudo chmod -R 755 /path/to/BLIMAS
sudo chmod 777 /path/to/BLIMAS/sensor_data.txt  # If using file-based data
```

## Configuration

### System Configuration

The system uses a database-driven configuration system. Key settings can be updated in the `system_config` table:

```sql
-- Update data refresh interval (milliseconds)
UPDATE system_config SET config_value = '3000' WHERE config_key = 'data_refresh_interval';

-- Update chart data points
UPDATE system_config SET config_value = '100' WHERE config_key = 'chart_data_points';

-- Update weather location
UPDATE system_config SET config_value = 'Colombo,LK' WHERE config_key = 'weather_location';
```

### Environment Variables (Optional)

For enhanced security, you can use environment variables:

Create `.env` file:
```
DB_HOST=localhost
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_DATABASE=blimas_db
WEATHER_API_KEY=your_api_key
```

Then update `includes/database.php` to use environment variables.

## Data Input

### Arduino/Sensor Integration

The system expects sensor data to be posted to `upload.php` with the following parameters:

- `water_temp1`: Water temperature at depth 1 (°C)
- `water_temp2`: Water temperature at depth 2 (°C)
- `water_temp3`: Water temperature at depth 3 (°C)
- `humidity`: Humidity percentage
- `air_temp`: Air temperature (°C)
- `water_level`: Water level (cm)
- `battery_level`: Battery level percentage (optional)

Example POST request:
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

### Manual Data Entry

You can also insert data directly into the database:

```sql
INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp1, water_temp2, water_temp3, battery_level) 
VALUES (28.5, 75.2, 120.5, 26.8, 25.9, 24.7, 85.3);
```

## API Endpoints

The system provides several API endpoints for real-time data:

### Get Latest Sensor Data
```
GET /api/data.php?action=latest
```

### Get Weather Data
```
GET /api/data.php?action=weather
```

### Get Historical Data
```
GET /api/data.php?action=historical&parameter=air_temperature&limit=50
```

### Get Water Temperature Data
```
GET /api/data.php?action=water_temperature&limit=50
```

### Get System Status
```
GET /api/data.php?action=status
```

## Monitoring & Maintenance

### Log Files

Monitor the following log files for issues:
- `/var/log/apache2/blimas_error.log` (Apache)
- `/var/log/nginx/error.log` (Nginx)
- PHP error logs

### Database Maintenance

```sql
-- Clean old weather cache (run daily)
DELETE FROM weather_cache WHERE expires_at < NOW();

-- Check database size
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'blimas_db';
```

### Performance Optimization

1. **Database Indexing**: The schema includes proper indexes
2. **Weather Caching**: Weather data is cached for 10 minutes
3. **Data Limiting**: Charts show only the latest 50 data points by default
4. **GZIP Compression**: Enable in web server configuration

## Troubleshooting

### Common Issues

#### Database Connection Errors
```
Error: Database connection failed
```
**Solution**: Check database credentials in `includes/database.php`

#### Weather API Errors
```
Weather API error: Invalid API key
```
**Solution**: Verify your OpenWeatherMap API key is correct and active

#### Charts Not Loading
```
Chart data is empty
```
**Solution**: 
1. Check if sensor data exists in the database
2. Verify database connection
3. Check browser console for JavaScript errors

#### Real-time Updates Not Working
**Solution**:
1. Check if `api/data.php` is accessible
2. Verify database connection
3. Check browser network tab for failed requests

### Debug Mode

Enable PHP error reporting for debugging:

```php
// Add to top of any PHP file
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Testing

#### Test Database Connection
```bash
php -r "
require_once 'includes/database.php';
if (DatabaseConfig::testConnection()) {
    echo 'Database connection successful\n';
} else {
    echo 'Database connection failed\n';
}
"
```

#### Test API Endpoints
```bash
# Test latest data
curl http://localhost/api/data.php?action=latest

# Test weather data
curl http://localhost/api/data.php?action=weather
```

## Security Considerations

1. **Database Security**: Use strong passwords and limit database user privileges
2. **API Security**: Consider implementing rate limiting
3. **SSL/HTTPS**: Use SSL certificates for production
4. **File Permissions**: Ensure proper file permissions
5. **Input Validation**: All inputs are validated and sanitized

## Backup & Recovery

### Database Backup
```bash
# Create backup
mysqldump -u username -p blimas_db > blimas_backup_$(date +%Y%m%d).sql

# Restore backup
mysql -u username -p blimas_db < blimas_backup_20240101.sql
```

### File Backup
```bash
# Backup entire application
tar -czf blimas_backup_$(date +%Y%m%d).tar.gz /path/to/BLIMAS
```

## Support

For issues and support:
1. Check this documentation
2. Review log files for error messages
3. Open an issue on the GitHub repository
4. Contact the development team

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source. Please check the repository for license details.