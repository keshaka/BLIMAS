<?php
/**
 * BLIMAS Weather API Integration
 * OpenWeatherMap API integration with caching
 */

require_once 'database.php';

class WeatherAPI {
    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';
    private $cacheExpiryMinutes = 10; // Cache weather data for 10 minutes
    
    public function __construct($apiKey = null) {
        // Get API key from config or parameter
        $this->apiKey = $apiKey ?: DatabaseConfig::getSystemConfig('weather_api_key');
        
        if (!$this->apiKey) {
            throw new Exception('OpenWeatherMap API key not configured');
        }
    }
    
    /**
     * Get weather data with caching
     */
    public function getWeatherData($location = null) {
        $location = $location ?: DatabaseConfig::getSystemConfig('weather_location') ?: 'Katubedda,LK';
        
        // Check cache first
        $cachedData = $this->getCachedWeatherData($location);
        if ($cachedData) {
            return $cachedData;
        }
        
        // Fetch fresh data from API
        $freshData = $this->fetchWeatherFromAPI($location);
        if ($freshData) {
            $this->cacheWeatherData($location, $freshData);
            return $freshData;
        }
        
        return null;
    }
    
    /**
     * Fetch weather data from OpenWeatherMap API
     */
    private function fetchWeatherFromAPI($location) {
        $url = sprintf(
            '%s?q=%s&appid=%s&units=metric',
            $this->baseUrl,
            urlencode($location),
            $this->apiKey
        );
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'BLIMAS Weather Monitor 1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            error_log("BLIMAS Weather API: Failed to fetch data from $url");
            return null;
        }
        
        $data = json_decode($response, true);
        if (!$data || isset($data['cod']) && $data['cod'] !== 200) {
            error_log("BLIMAS Weather API: Invalid response - " . ($data['message'] ?? 'Unknown error'));
            return null;
        }
        
        return $this->formatWeatherData($data);
    }
    
    /**
     * Format weather data for consistent output
     */
    private function formatWeatherData($apiData) {
        return [
            'location' => $apiData['name'] . ', ' . $apiData['sys']['country'],
            'temperature' => round($apiData['main']['temp'], 1),
            'humidity' => $apiData['main']['humidity'],
            'wind_speed' => isset($apiData['wind']['speed']) ? round($apiData['wind']['speed'] * 3.6, 1) : 0, // Convert m/s to km/h
            'wind_direction' => isset($apiData['wind']['deg']) ? $this->degreeToCompass($apiData['wind']['deg']) : 'N/A',
            'precipitation' => isset($apiData['rain']['1h']) ? $apiData['rain']['1h'] : 0,
            'weather_condition' => $apiData['weather'][0]['main'],
            'weather_description' => $apiData['weather'][0]['description'],
            'icon' => $apiData['weather'][0]['icon'],
            'pressure' => $apiData['main']['pressure'],
            'visibility' => isset($apiData['visibility']) ? $apiData['visibility'] / 1000 : null, // Convert to km
            'timestamp' => date('Y-m-d H:i:s'),
            'api_response' => json_encode($apiData)
        ];
    }
    
    /**
     * Get cached weather data
     */
    private function getCachedWeatherData($location) {
        try {
            $conn = DatabaseConfig::getMySQLiConnection();
            $stmt = $conn->prepare(
                "SELECT * FROM weather_cache 
                 WHERE location = ? AND expires_at > NOW() 
                 ORDER BY timestamp DESC LIMIT 1"
            );
            $stmt->bind_param("s", $location);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $conn->close();
            
            if ($row) {
                // Remove database-specific fields
                unset($row['id'], $row['expires_at'], $row['api_response']);
                return $row;
            }
        } catch (Exception $e) {
            error_log("BLIMAS Weather Cache Error: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Cache weather data
     */
    private function cacheWeatherData($location, $data) {
        try {
            $conn = DatabaseConfig::getMySQLiConnection();
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->cacheExpiryMinutes} minutes"));
            
            $stmt = $conn->prepare(
                "INSERT INTO weather_cache 
                 (location, temperature, humidity, wind_speed, wind_direction, precipitation, 
                  weather_condition, weather_description, api_response, expires_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->bind_param(
                "sdddsdssss",
                $location,
                $data['temperature'],
                $data['humidity'],
                $data['wind_speed'],
                $data['wind_direction'],
                $data['precipitation'],
                $data['weather_condition'],
                $data['weather_description'],
                $data['api_response'],
                $expiresAt
            );
            
            $stmt->execute();
            $conn->close();
        } catch (Exception $e) {
            error_log("BLIMAS Weather Cache Save Error: " . $e->getMessage());
        }
    }
    
    /**
     * Convert wind degree to compass direction
     */
    private function degreeToCompass($deg) {
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = round($deg / 22.5) % 16;
        return $directions[$index];
    }
    
    /**
     * Clean old cache entries
     */
    public static function cleanCache() {
        try {
            $conn = DatabaseConfig::getMySQLiConnection();
            $conn->query("DELETE FROM weather_cache WHERE expires_at < NOW()");
            $conn->close();
        } catch (Exception $e) {
            error_log("BLIMAS Weather Cache Clean Error: " . $e->getMessage());
        }
    }
}
?>