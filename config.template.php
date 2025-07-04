<?php
/**
 * BLIMAS Configuration Template
 * Copy this file to config.php and update the values
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', '');  // Your database username
define('DB_PASSWORD', '');  // Your database password
define('DB_DATABASE', 'blimas_db');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// Weather API Configuration
define('WEATHER_API_KEY', '');  // Your OpenWeatherMap API key
define('WEATHER_LOCATION', 'Katubedda,LK');  // Weather location

// System Configuration
define('DATA_REFRESH_INTERVAL', 5000);  // Data refresh interval in milliseconds
define('CHART_DATA_POINTS', 50);  // Number of data points to show in charts
define('WEATHER_CACHE_MINUTES', 10);  // Weather cache duration in minutes

// System Information
define('SYSTEM_NAME', 'BLIMAS');
define('SYSTEM_DESCRIPTION', 'Bolgoda Lake Information Monitoring & Analysis System');
define('SYSTEM_TIMEZONE', 'Asia/Colombo');

// Security Configuration
define('ENABLE_DEBUG', false);  // Set to true for development, false for production
define('LOG_ERRORS', true);  // Enable error logging
define('DISPLAY_ERRORS', false);  // Display errors on screen (development only)

// Email Configuration (Optional - for future notifications)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('NOTIFICATION_EMAIL', '');

// Advanced Configuration
define('MAX_RECORDS_PER_REQUEST', 200);  // Maximum records returned by API
define('SESSION_TIMEOUT', 3600);  // Session timeout in seconds
define('ENABLE_COMPRESSION', true);  // Enable GZIP compression
define('CACHE_STATIC_FILES', true);  // Enable static file caching

// Development Configuration
if (ENABLE_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', DISPLAY_ERRORS);
    ini_set('log_errors', LOG_ERRORS);
}

// Production optimizations
if (!ENABLE_DEBUG) {
    // Disable error display in production
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    // Enable output compression
    if (ENABLE_COMPRESSION && extension_loaded('zlib')) {
        ob_start('ob_gzhandler');
    }
}
?>