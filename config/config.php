<?php
// General configuration settings
define('SITE_NAME', 'BLIMAS');
define('SITE_DESCRIPTION', 'Bolgoda Lake Information Monitoring & Analysis System');
define('SITE_URL', 'https://webpojja.pasgorasa.site');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('ADMIN_COOKIE_NAME', 'blimas_admin_remember');
define('ADMIN_COOKIE_LIFETIME', 30 * 24 * 60 * 60); // 30 days

// Weather API settings
define('WEATHER_API_KEY', ''); // Add your OpenWeatherMap API key here
define('WEATHER_LOCATION', 'Katubedda,LK');
define('WEATHER_CACHE_TIME', 600); // 10 minutes

// Data refresh intervals
define('SENSOR_DATA_REFRESH', 30); // 30 seconds
define('CHART_DATA_LIMIT', 50); // Number of data points to show in charts

// Battery level thresholds
define('BATTERY_GOOD', 70);
define('BATTERY_WARN', 30);

// Time zone
date_default_timezone_set('Asia/Colombo');

// Error reporting (disable in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}
?>