<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check if we have a cached weather response (within last 10 minutes)
    $stmt = $conn->prepare("SELECT weather_data, cached_at FROM weather_cache 
                           WHERE location = ? AND cached_at > DATE_SUB(NOW(), INTERVAL ? SECOND) 
                           ORDER BY cached_at DESC LIMIT 1");
    $stmt->execute([WEATHER_LOCATION, WEATHER_CACHE_TIME]);
    $cached = $stmt->fetch();
    
    if ($cached) {
        // Return cached data
        $weather_data = json_decode($cached['weather_data'], true);
        $weather_data['cached'] = true;
        $weather_data['cached_at'] = $cached['cached_at'];
        echo json_encode($weather_data, JSON_PRETTY_PRINT);
        exit;
    }
    
    // If we have an API key, fetch fresh data
    if (WEATHER_API_KEY && WEATHER_API_KEY !== '') {
        $api_url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode(WEATHER_LOCATION) . "&appid=" . WEATHER_API_KEY . "&units=metric";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'BLIMAS Weather Monitor'
            ]
        ]);
        
        $api_response = @file_get_contents($api_url, false, $context);
        
        if ($api_response !== false) {
            $weather_data = json_decode($api_response, true);
            
            if (isset($weather_data['main'])) {
                // Format the response
                $formatted_response = [
                    'success' => true,
                    'location' => $weather_data['name'] ?? 'Katubedda',
                    'country' => $weather_data['sys']['country'] ?? 'LK',
                    'temperature' => round($weather_data['main']['temp'], 1),
                    'feels_like' => round($weather_data['main']['feels_like'], 1),
                    'humidity' => $weather_data['main']['humidity'],
                    'pressure' => $weather_data['main']['pressure'],
                    'description' => $weather_data['weather'][0]['description'] ?? 'Unknown',
                    'icon' => $weather_data['weather'][0]['icon'] ?? '01d',
                    'wind_speed' => $weather_data['wind']['speed'] ?? 0,
                    'wind_direction' => $weather_data['wind']['deg'] ?? 0,
                    'clouds' => $weather_data['clouds']['all'] ?? 0,
                    'visibility' => $weather_data['visibility'] ?? null,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'cached' => false
                ];
                
                // Cache the response
                $stmt = $conn->prepare("INSERT INTO weather_cache (location, weather_data) VALUES (?, ?)");
                $stmt->execute([WEATHER_LOCATION, json_encode($formatted_response)]);
                
                echo json_encode($formatted_response, JSON_PRETTY_PRINT);
                exit;
            }
        }
    }
    
    // Fallback response when API is not available or configured
    $fallback_response = [
        'success' => true,
        'location' => 'Katubedda',
        'country' => 'LK',
        'temperature' => 28.0,
        'feels_like' => 30.5,
        'humidity' => 75,
        'pressure' => 1013,
        'description' => 'partly cloudy',
        'icon' => '02d',
        'wind_speed' => 3.5,
        'wind_direction' => 180,
        'clouds' => 25,
        'visibility' => 10000,
        'timestamp' => date('Y-m-d H:i:s'),
        'cached' => false,
        'fallback' => true,
        'message' => 'Weather API not configured - showing sample data'
    ];
    
    echo json_encode($fallback_response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Weather API error: " . $e->getMessage());
    
    $response = [
        'success' => false,
        'error' => 'Weather service temporarily unavailable',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    http_response_code(500);
    echo json_encode($response, JSON_PRETTY_PRINT);
}
?>