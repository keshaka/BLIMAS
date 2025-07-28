<?php
class GeminiAPI {
    private $api_key;
    private $base_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    public function __construct() {
        // You can set your Gemini API key here or use environment variable
        $this->api_key = getenv('GEMINI_API_KEY') ?: 'YOUR_GEMINI_API_KEY_HERE';
    }
    
    public function generateAnalysis($prompt) {
        if ($this->api_key === 'YOUR_GEMINI_API_KEY_HERE') {
            return [
                'error' => true,
                'message' => 'Gemini API key not configured. Please set your API key in config/gemini.php'
            ];
        }
        
        $headers = [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->api_key
        ];
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 1000
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            curl_close($ch);
            return [
                'error' => true,
                'message' => 'cURL Error: ' . curl_error($ch)
            ];
        }
        
        curl_close($ch);
        
        if ($http_code !== 200) {
            return [
                'error' => true,
                'message' => 'API Error: HTTP ' . $http_code . ' - ' . $response
            ];
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => true,
                'message' => 'JSON Decode Error: ' . json_last_error_msg()
            ];
        }
        
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'error' => false,
                'analysis' => $decoded['candidates'][0]['content']['parts'][0]['text']
            ];
        }
        
        return [
            'error' => true,
            'message' => 'Unexpected API response format'
        ];
    }
    
    public function analyzeSensorData($sensorData, $analysisType = 'general') {
        $prompt = $this->buildAnalysisPrompt($sensorData, $analysisType);
        return $this->generateAnalysis($prompt);
    }
    
    private function buildAnalysisPrompt($sensorData, $analysisType) {
        $dataStats = $this->calculateDataStats($sensorData);
        
        $prompt = "You are an environmental data analyst specializing in lake monitoring systems. ";
        $prompt .= "Please analyze the following Bolgoda Lake sensor data and provide insights:\n\n";
        
        $prompt .= "Data Summary:\n";
        $prompt .= "- Data points: " . count($sensorData) . "\n";
        $prompt .= "- Time period: " . $dataStats['time_range'] . "\n";
        $prompt .= "- Air Temperature: avg {$dataStats['air_temp']['avg']}°C, range {$dataStats['air_temp']['min']}°C to {$dataStats['air_temp']['max']}°C\n";
        $prompt .= "- Humidity: avg {$dataStats['humidity']['avg']}%, range {$dataStats['humidity']['min']}% to {$dataStats['humidity']['max']}%\n";
        $prompt .= "- Water Level: avg {$dataStats['water_level']['avg']}m, range {$dataStats['water_level']['min']}m to {$dataStats['water_level']['max']}m\n";
        $prompt .= "- Water Temperature (Surface): avg {$dataStats['water_temp_1']['avg']}°C\n";
        $prompt .= "- Water Temperature (Middle): avg {$dataStats['water_temp_2']['avg']}°C\n";
        $prompt .= "- Water Temperature (Bottom): avg {$dataStats['water_temp_3']['avg']}°C\n\n";
        
        switch ($analysisType) {
            case 'trends':
                $prompt .= "Focus on trend analysis: Identify patterns, seasonal variations, and significant changes in the data.";
                break;
            case 'predictions':
                $prompt .= "Focus on predictions: Based on the data trends, predict likely future conditions and potential environmental changes.";
                break;
            case 'anomalies':
                $prompt .= "Focus on anomaly detection: Identify unusual readings, potential equipment issues, or environmental concerns.";
                break;
            case 'insights':
                $prompt .= "Focus on environmental insights: Provide ecological interpretations and recommendations for lake management.";
                break;
            default:
                $prompt .= "Provide a comprehensive analysis including trends, anomalies, and recommendations.";
        }
        
        $prompt .= "\n\nPlease structure your response with clear headings and actionable insights.";
        
        return $prompt;
    }
    
    private function calculateDataStats($data) {
        if (empty($data)) {
            return [];
        }
        
        $stats = [];
        $fields = ['air_temperature', 'humidity', 'water_level', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3'];
        $fieldMap = [
            'air_temperature' => 'air_temp',
            'humidity' => 'humidity',
            'water_level' => 'water_level',
            'water_temp_depth1' => 'water_temp_1',
            'water_temp_depth2' => 'water_temp_2',
            'water_temp_depth3' => 'water_temp_3'
        ];
        
        foreach ($fields as $field) {
            $values = array_filter(array_column($data, $field), function($val) {
                return $val !== null && is_numeric($val);
            });
            
            if (!empty($values)) {
                $stats[$fieldMap[$field]] = [
                    'avg' => round(array_sum($values) / count($values), 2),
                    'min' => round(min($values), 2),
                    'max' => round(max($values), 2)
                ];
            }
        }
        
        // Calculate time range
        $timestamps = array_column($data, 'timestamp');
        if (!empty($timestamps)) {
            $start = min($timestamps);
            $end = max($timestamps);
            $stats['time_range'] = date('Y-m-d H:i', strtotime($start)) . ' to ' . date('Y-m-d H:i', strtotime($end));
        }
        
        return $stats;
    }
}
?>