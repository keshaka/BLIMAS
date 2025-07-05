<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/weather.php';

$weather = new WeatherAPI();
$weather_data = $weather->getWeather();

if ($weather_data && isset($weather_data['main'])) {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'temperature' => $weather_data['main']['temp'],
            'humidity' => $weather_data['main']['humidity'],
            'pressure' => $weather_data['main']['pressure'],
            'description' => $weather_data['weather'][0]['description'],
            'icon' => $weather_data['weather'][0]['icon'],
            'city' => $weather_data['name']
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to fetch weather data'
    ]);
}
?>