<?php
/**
 * Weather API integration for Katubedda, Sri Lanka
 * BLIMAS - Bolgoda Lake Information Monitoring & Analysis System
 */

require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

function getWeatherData() {
    // For demo purposes, we'll use a free weather API
    // You can replace this with your preferred weather API
    $api_key = WEATHER_API_KEY; // You'll need to get this from weatherapi.com
    $location = LOCATION;
    
    // If no API key is set, return mock data
    if ($api_key === 'your_weather_api_key_here') {
        return getMockWeatherData();
    }
    
    $url = WEATHER_API_URL . "?key={$api_key}&q={$location}&aqi=no";
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $weather_data = json_decode($response, true);
            
            if (isset($weather_data['current'])) {
                return [
                    'success' => true,
                    'data' => [
                        'location' => $weather_data['location']['name'] . ', ' . $weather_data['location']['country'],
                        'temperature' => $weather_data['current']['temp_c'],
                        'condition' => $weather_data['current']['condition']['text'],
                        'humidity' => $weather_data['current']['humidity'],
                        'wind_speed' => $weather_data['current']['wind_kph'],
                        'wind_direction' => $weather_data['current']['wind_dir'],
                        'pressure' => $weather_data['current']['pressure_mb'],
                        'visibility' => $weather_data['current']['vis_km'],
                        'uv_index' => $weather_data['current']['uv'],
                        'feels_like' => $weather_data['current']['feelslike_c'],
                        'precipitation' => $weather_data['current']['precip_mm'],
                        'icon' => $weather_data['current']['condition']['icon']
                    ],
                    'last_updated' => $weather_data['current']['last_updated']
                ];
            }
        }
        
        throw new Exception('Invalid weather API response');
        
    } catch (Exception $e) {
        return getMockWeatherData();
    }
}

function getMockWeatherData() {
    // Mock weather data for demonstration
    return [
        'success' => true,
        'data' => [
            'location' => 'Katubedda, Sri Lanka',
            'temperature' => rand(26, 32),
            'condition' => 'Partly Cloudy',
            'humidity' => rand(70, 85),
            'wind_speed' => rand(5, 15),
            'wind_direction' => 'SW',
            'pressure' => rand(1010, 1020),
            'visibility' => rand(8, 12),
            'uv_index' => rand(3, 8),
            'feels_like' => rand(28, 35),
            'precipitation' => rand(0, 5),
            'icon' => '//cdn.weatherapi.com/weather/64x64/day/116.png'
        ],
        'last_updated' => date('Y-m-d H:i:s'),
        'note' => 'Using mock data - configure WEATHER_API_KEY for live data'
    ];
}

try {
    $weather = getWeatherData();
    echo json_encode($weather);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching weather data: ' . $e->getMessage()
    ]);
}
?>