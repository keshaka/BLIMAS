<?php
// Mock Gemini API for development and testing
class GeminiAPISimulator {
    public function __construct($api_key = null) {
        // Mock constructor
    }
    
    public function generateContent($prompt, $temperature = 0.7) {
        // Return mock AI responses based on prompt type
        if (strpos($prompt, 'trend') !== false) {
            return json_encode([
                'trends' => [
                    'air_temperature' => 'Showing gradual increase of 0.2°C over the monitoring period',
                    'humidity' => 'Stable levels around 75% with minor fluctuations',
                    'water_level' => 'Consistent at 2.45m with seasonal variation patterns'
                ],
                'patterns' => [
                    'daily_cycle' => 'Clear diurnal temperature patterns observed',
                    'depth_stratification' => 'Normal thermal stratification in water column'
                ],
                'insights' => [
                    'Environmental conditions are within normal parameters',
                    'Lake ecosystem shows healthy thermal dynamics',
                    'No significant environmental stress indicators detected'
                ],
                'health_status' => 'excellent'
            ]);
        }
        
        if (strpos($prompt, 'anomal') !== false) {
            return json_encode([
                'anomalies' => [
                    'detected' => 'Minor temperature spike at 15:30 - likely weather related',
                    'water_level' => 'No significant anomalies detected'
                ],
                'severity_levels' => [
                    'overall' => 'low',
                    'critical_alerts' => 0
                ],
                'recommendations' => [
                    'Continue regular monitoring',
                    'Monitor weather patterns for correlation'
                ],
                'risk_assessment' => 'minimal_risk'
            ]);
        }
        
        if (strpos($prompt, 'prediction') !== false) {
            return json_encode([
                'predictions' => [
                    'air_temperature' => [
                        'next_24h' => '28-30°C range expected',
                        'trend' => 'slight_increase'
                    ],
                    'humidity' => [
                        'next_24h' => '70-80% range',
                        'trend' => 'stable'
                    ],
                    'water_level' => [
                        'next_24h' => 'Stable around 2.45m',
                        'trend' => 'stable'
                    ]
                ],
                'confidence_levels' => [
                    'temperature' => 'high',
                    'humidity' => 'medium',
                    'water_level' => 'high'
                ],
                'factors' => [
                    'weather_forecast' => 'Partly cloudy conditions expected',
                    'seasonal_patterns' => 'Normal summer conditions'
                ],
                'recommendations' => [
                    'Continue regular monitoring schedule',
                    'Prepare for potential afternoon temperature peaks'
                ]
            ]);
        }
        
        if (strpos($prompt, 'summary') !== false) {
            return json_encode([
                'overview' => [
                    'status' => 'All systems operating normally',
                    'data_quality' => 'Excellent sensor coverage and reliability',
                    'environmental_health' => 'Lake ecosystem in good condition'
                ],
                'metrics' => [
                    'avg_air_temp' => '28.5°C',
                    'avg_humidity' => '75.2%',
                    'avg_water_level' => '2.45m',
                    'thermal_stratification' => 'Normal (2.7°C difference surface to bottom)'
                ],
                'indicators' => [
                    'water_quality' => 'good',
                    'thermal_stability' => 'excellent',
                    'level_stability' => 'stable'
                ],
                'changes' => [
                    'significant_trends' => 'Gradual seasonal warming as expected',
                    'notable_events' => 'No major environmental events recorded'
                ],
                'recommendations' => [
                    'Maintain current monitoring frequency',
                    'Schedule quarterly water quality assessment',
                    'Continue tracking thermal stratification patterns'
                ]
            ]);
        }
        
        // Default response
        return json_encode([
            'analysis' => 'Mock AI analysis response',
            'confidence' => 'medium',
            'note' => 'This is a simulated response for development purposes'
        ]);
    }
    
    public function analyzeSensorData($sensorData, $analysisType = 'trend') {
        // Mock the specific analysis types
        switch ($analysisType) {
            case 'trend':
                return $this->generateContent('trend analysis', 0.3);
            case 'anomaly':
                return $this->generateContent('anomaly detection', 0.2);
            case 'prediction':
                return $this->generateContent('prediction forecast', 0.4);
            case 'summary':
                return $this->generateContent('summary report', 0.3);
            default:
                return $this->generateContent('general analysis', 0.5);
        }
    }
    
    public function validateApiKey() {
        return true; // Always return true for mock
    }
}

// For development, use the simulator instead of real API
class GeminiAPI extends GeminiAPISimulator {
    // This will use the simulator functionality
}
?>