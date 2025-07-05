<?php
class WeatherAPI {
    private $api_key = 'YOUR_OPENWEATHER_API_KEY'; // Get from openweathermap.org
    private $base_url = 'https://api.openweathermap.org/data/2.5/weather';
    
    public function getWeather($city = 'Katubedda,LK') {
        $url = $this->base_url . "?q=" . urlencode($city) . "&appid=" . $this->api_key . "&units=metric";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
?>