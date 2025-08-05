# BLIMAS - Bolgoda Lake Information Monitoring and Analysis System

A comprehensive PHP-based environmental monitoring system for real-time lake data visualization and analysis.

## Features

- **Real-time Dashboard**: Live sensor data with auto-refresh every 5 minutes
- **Interactive Charts**: Historical data visualization using Chart.js
- **Responsive Design**: Mobile-friendly interface with Bootstrap 5
- **Beautiful Animations**: Smooth transitions using AOS and custom CSS
- **RESTful API**: Clean API endpoints for data retrieval
- **Multiple Time Periods**: Day, week, and month data filtering

## Installation

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Setup Steps

1. **Clone/Download the project**
   ```bash
   git clone <repository-url>
   # Or download and extract the ZIP file
   ```

2. **Database Setup**
   ```sql
   -- Create the database
   CREATE DATABASE IF NOT EXISTS blimas_db;
   
   -- Use the database
   USE blimas_db;
   
   -- Create the sensor_data table
   CREATE TABLE IF NOT EXISTS sensor_data (
       id INT AUTO_INCREMENT PRIMARY KEY,
       air_temperature DECIMAL(5,2),
       humidity DECIMAL(5,2),
       water_level DECIMAL(8,2),
       water_temp_depth1 DECIMAL(5,2),
       water_temp_depth2 DECIMAL(5,2),
       water_temp_depth3 DECIMAL(5,2),
       timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
   );
   ```

3. **Configure Database Connection**
   Edit `config/database.php`:
   ```php
   private $host = "localhost";
   private $db_name = "blimas_db";
   private $username = "your_username";
   private $password = "your_password";
   ```

4. **Set Permissions**
   ```bash
   chmod 755 api/
   chmod 644 config/database.php
   ```

5. **Insert Sample Data (Optional)**
   ```bash
   php sample_data.php
   ```

## Project Structure

```
BLIMAS/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
├── api/
│   ├── get_latest_data.php  # Latest sensor data
│   ├── get_historical_data.php # Historical data
│   └── get_water_temp_data.php # Water temperature data
├── assets/
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   └── js/
│       ├── main.js          # Core JavaScript
│       ├── dashboard.js     # Dashboard functionality
│       ├── air-temperature.js
│       ├── humidity.js
│       ├── water-level.js
│       └── water-temperature.js
├── index.php                # Homepage/Dashboard
├── air-temperature.php      # Air temperature page
├── humidity.php             # Humidity page
├── water-level.php          # Water level page
├── water-temperature.php    # Water temperature page
├── sample_data.php          # Sample data generator
├── .htaccess               # Apache configuration
├── 404.php                 # Error page
└── README.md               # This file
```

## API Endpoints

### Get Latest Data
```
GET /api/get_latest_data.php
```
Returns the most recent sensor readings.

### Get Historical Data
```
GET /api/get_historical_data.php?type={sensor_type}&period={time_period}
```
Parameters:
- `type`: air_temperature, humidity, water_level
- `period`: day, week, month

### Get Water Temperature Data
```
GET /api/get_water_temp_data.php?period={time_period}
```
Returns temperature data for all three depths.

## Customization

### Colors and Styling
Edit `assets/css/style.css` to modify:
- Color variables in `:root`
- Card gradients
- Animation effects

### Data Refresh Rate
Modify the refresh interval in `assets/js/dashboard.js`:
```javascript
this.updateInterval = 5 * 60 * 1000; // 5 minutes in milliseconds
```

### Chart Configuration
Customize charts in respective JavaScript files:
- `ChartDefaults` object in `main.js`
- Individual chart configurations

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Dependencies

### Frontend
- Bootstrap 5.3.0
- Chart.js (latest)
- Font Awesome 6.4.0
- AOS (Animate On Scroll) 2.3.1

### Backend
- PHP 7.4+
- PDO MySQL Extension

## Security Features

- SQL injection prevention with prepared statements
- XSS protection headers
- CSRF protection ready
- File access restrictions
- Input validation and sanitization

## Performance Optimization

- Gzip compression enabled
- Browser caching configured
- Minified CSS/JS (production ready)
- Efficient database queries
- Lazy loading animations

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running

2. **Charts Not Loading**
   - Check browser console for JavaScript errors
   - Verify API endpoints are accessible

3. **Data Not Updating**
   - Check if sample data exists in database
   - Verify API responses in browser DevTools

4. **Styling Issues**
   - Clear browser cache
   - Check if CSS files are loading properly

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Check the documentation
- Review the code comments
- Test with sample data first

## Changelog

### Version 1.0.0 (2024-08-03)
- Initial release
- Real-time dashboard
- Historical data charts
- Responsive design
- API endpoints
- Sample data generator