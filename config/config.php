<?php
/**
 * Configuration file for BLIMAS
 * Bolgoda Lake Information Monitoring & Analysis System
 */

// Site Configuration
define('SITE_NAME', 'BLIMAS');
define('SITE_DESCRIPTION', 'Bolgoda Lake Information Monitoring & Analysis System');
define('SITE_URL', 'http://blimas.pasgorasa.site');

// Weather API Configuration
define('WEATHER_API_KEY', 'your_weather_api_key_here'); // Replace with actual API key
define('WEATHER_API_URL', 'http://api.weatherapi.com/v1/current.json');
define('LOCATION', 'Katubedda,Sri Lanka');

// Data refresh intervals (in milliseconds)
define('DATA_REFRESH_INTERVAL', 5000); // 5 seconds
define('CHART_REFRESH_INTERVAL', 30000); // 30 seconds

// Alert thresholds
define('TEMP_HIGH_THRESHOLD', 35); // °C
define('TEMP_LOW_THRESHOLD', 15); // °C
define('HUMIDITY_HIGH_THRESHOLD', 90); // %
define('HUMIDITY_LOW_THRESHOLD', 30); // %
define('WATER_LEVEL_HIGH_THRESHOLD', 200); // cm
define('WATER_LEVEL_LOW_THRESHOLD', 50); // cm

// Timezone
date_default_timezone_set('Asia/Colombo');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>