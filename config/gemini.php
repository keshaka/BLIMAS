<?php
class GeminiAPI {
    private $api_key = 'YOUR_GEMINI_API_KEY'; // Get from Google AI Studio
    private $base_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    public function __construct($api_key = null) {
        if ($api_key) {
            $this->api_key = $api_key;
        }
    }
    
    public function generateContent($prompt, $temperature = 0.7) {
        $url = $this->base_url . '?key=' . $this->api_key;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API request failed with HTTP code: ' . $httpCode);
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid API response format');
        }
        
        return $result['candidates'][0]['content']['parts'][0]['text'];
    }
    
    public function analyzeSensorData($sensorData, $analysisType = 'trend') {
        switch ($analysisType) {
            case 'trend':
                return $this->generateTrendAnalysis($sensorData);
            case 'anomaly':
                return $this->generateAnomalyDetection($sensorData);
            case 'prediction':
                return $this->generatePrediction($sensorData);
            case 'summary':
                return $this->generateSummaryReport($sensorData);
            default:
                throw new Exception('Invalid analysis type');
        }
    }
    
    private function generateTrendAnalysis($sensorData) {
        $dataString = $this->formatSensorDataForAnalysis($sensorData);
        
        $prompt = "Analyze the following lake monitoring sensor data for trends and patterns. 
        Provide insights on air temperature, humidity, water level, and water temperature at different depths.
        Focus on identifying trends, correlations, and environmental patterns:

        {$dataString}

        Please provide a structured analysis with:
        1. Temperature trends (air and water)
        2. Humidity patterns
        3. Water level variations
        4. Depth temperature relationships
        5. Overall environmental health assessment

        Return the response as a JSON object with keys: trends, patterns, insights, health_status.";
        
        return $this->generateContent($prompt, 0.3);
    }
    
    private function generateAnomalyDetection($sensorData) {
        $dataString = $this->formatSensorDataForAnalysis($sensorData);
        
        $prompt = "Analyze the following lake monitoring data for anomalies and unusual patterns.
        Identify any readings that seem abnormal compared to typical lake environmental conditions:

        {$dataString}

        Please identify:
        1. Temperature anomalies (unusually high/low readings)
        2. Humidity irregularities
        3. Water level concerns
        4. Unusual depth temperature patterns
        5. Risk assessment for lake ecosystem

        Return as JSON with keys: anomalies, severity_levels, recommendations, risk_assessment.";
        
        return $this->generateContent($prompt, 0.2);
    }
    
    private function generatePrediction($sensorData) {
        $dataString = $this->formatSensorDataForAnalysis($sensorData);
        
        $prompt = "Based on the following lake monitoring data trends, provide predictions for the next 24-48 hours.
        Consider seasonal patterns, environmental factors, and data trends:

        {$dataString}

        Provide predictions for:
        1. Air temperature range
        2. Humidity levels
        3. Water level changes
        4. Water temperature variations by depth
        5. Weather-related impacts

        Return as JSON with keys: predictions, confidence_levels, factors, recommendations.";
        
        return $this->generateContent($prompt, 0.4);
    }
    
    private function generateSummaryReport($sensorData) {
        $dataString = $this->formatSensorDataForAnalysis($sensorData);
        
        $prompt = "Create a comprehensive summary report of this lake monitoring data.
        Provide executive-level insights suitable for environmental monitoring:

        {$dataString}

        Include:
        1. Current status overview
        2. Key metrics summary
        3. Environmental health indicators
        4. Notable changes or trends
        5. Action items or recommendations

        Return as JSON with keys: overview, metrics, indicators, changes, recommendations.";
        
        return $this->generateContent($prompt, 0.3);
    }
    
    private function formatSensorDataForAnalysis($sensorData) {
        $formatted = "Lake Monitoring Data:\n";
        $formatted .= "===================\n\n";
        
        foreach ($sensorData as $index => $reading) {
            $formatted .= "Reading " . ($index + 1) . " (" . $reading['timestamp'] . "):\n";
            $formatted .= "- Air Temperature: " . $reading['air_temperature'] . "°C\n";
            $formatted .= "- Humidity: " . $reading['humidity'] . "%\n";
            $formatted .= "- Water Level: " . $reading['water_level'] . "m\n";
            $formatted .= "- Water Temp (Surface): " . $reading['water_temp_depth1'] . "°C\n";
            $formatted .= "- Water Temp (Middle): " . $reading['water_temp_depth2'] . "°C\n";
            $formatted .= "- Water Temp (Bottom): " . $reading['water_temp_depth3'] . "°C\n";
            $formatted .= "\n";
        }
        
        return $formatted;
    }
    
    public function validateApiKey() {
        try {
            $response = $this->generateContent("Test connection", 0.1);
            return !empty($response);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>