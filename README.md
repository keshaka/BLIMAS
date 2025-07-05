# BLIMAS - Bolgoda Lake Monitoring System

A comprehensive web-based monitoring system for tracking environmental parameters of Bolgoda Lake including air temperature, humidity, water level, and water temperature at multiple depths.

## Features

- **Real-time Dashboard**: Live monitoring of all sensor data
- **Weather Integration**: Current weather conditions for Katubedda, Sri Lanka
- **Historical Data Visualization**: Interactive charts with multiple time ranges
- **Multi-depth Water Temperature**: Monitoring at 3 different water depths
- **Responsive Design**: Mobile-friendly interface with smooth animations
- **Real-time Updates**: Automatic data refresh every 30 seconds

## Technical Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript ES6
- **Charts**: Chart.js
- **Weather API**: OpenWeatherMap

## Installation

### Prerequisites

- Ubuntu VPS with Apache/Nginx
- PHP 7.4 or higher
- MySQL 5.7 or higher
- cURL extension enabled

### Setup Instructions

1. **Clone the repository**
```bash
git clone https://github.com/keshaka/BLIMAS.git
cd BLIMAS
```

2. **Database Setup**
```bash
mysql -u root -p < database/schema.sql
```

3. **Configure Database Connection**
Edit `config/database.php` and update your database credentials:
```php
private $host = 'localhost';
private $db_name = 'blimas_db';
private $username = 'your_username';
private $password = 'your_password';
```

4. **Weather API Setup**
- Get a free API key from [OpenWeatherMap](https://openweathermap.org/api)
- Edit `config/weather.php` and add your API key:
```php
private $api_key = 'YOUR_OPENWEATHER_API_KEY';
```

5. **Web Server Configuration**

**For Apache:**
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/BLIMAS
    
    <Directory /var/www/html/BLIMAS>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**For Nginx:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/BLIMAS;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

6. **Set Permissions**
```bash
sudo chown -R www-data:www-data /var/www/html/BLIMAS
sudo chmod -R 755 /var/www/html/BLIMAS
```

7. **Restart Web Server**
```bash
# For Apache
sudo systemctl restart apache2

# For Nginx
sudo systemctl restart nginx
```

## API Endpoints

- `GET /api/get_sensor_data.php` - Latest sensor readings
- `GET /api/get_historical_data.php?type={type}&hours={hours}` - Historical data
- `GET /api/get_weather.php` - Current weather data

## Data Input

To add sensor data, insert records into the `sensor_data` table:

```sql
INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3) 
VALUES (28.5, 75.2, 2.45, 26.8, 25.9, 24.1);
```

## Customization

### Adding New Sensors

1. Add columns to the `sensor_data` table
2. Update API endpoints in `api/` directory
3. Modify the dashboard display in `index.php`
4. Add corresponding chart pages

### Styling

- Main styles: `assets/css/style.css`
- Responsive breakpoints already configured
- CSS animations and transitions included

## Monitoring and Maintenance

### Database Maintenance

```sql
-- Clean old data (older than 30 days)
DELETE FROM sensor_data WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Optimize table
OPTIMIZE TABLE sensor_data;
```

### Log Monitoring

Check web server logs for any errors:
```bash
# Apache
sudo tail -f /var/log/apache2/error.log

# Nginx
sudo tail -f /var/log/nginx/error.log
```

## Security Considerations

1. **Database Security**
   - Use strong passwords
   - Limit database user permissions
   - Regular backups

2. **API Security**
   - Implement rate limiting
   - Add authentication for data insertion
   - Validate all input data

3. **Web Security**
   - Keep PHP updated
   - Use HTTPS in production
   - Regular security updates

## Troubleshooting

### Common Issues

1. **Charts not loading**
   - Check browser console for errors
   - Verify Chart.js CDN is accessible
   - Check API endpoints are returning data

2. **Weather data not showing**
   - Verify OpenWeatherMap API key
   - Check network connectivity
   - Review API response in browser dev tools

3. **Database connection errors**
   - Verify database credentials
   - Check MySQL service status
   - Review PHP error logs

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull