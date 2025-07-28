<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/gemini_simulator.php'; // Use simulator for development

class AnalysisService {
    private $db;
    private $gemini;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->gemini = new GeminiAPI();
    }
    
    public function getRecentData($hours = 24, $limit = 50) {
        try {
            $query = "SELECT * FROM sensor_data 
                     WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR) 
                     ORDER BY timestamp DESC 
                     LIMIT ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $hours, PDO::PARAM_INT);
            $stmt->bindParam(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function getTrendAnalysis($hours = 24) {
        try {
            $data = $this->getRecentData($hours);
            
            if (empty($data)) {
                throw new Exception("No data available for analysis");
            }
            
            // Get AI analysis
            $aiAnalysis = $this->gemini->analyzeSensorData($data, 'trend');
            
            // Calculate statistical trends
            $statistics = $this->calculateStatistics($data);
            
            // Combine AI insights with statistical data
            $result = [
                'ai_analysis' => $this->parseAIResponse($aiAnalysis),
                'statistics' => $statistics,
                'data_points' => count($data),
                'time_range' => $hours . ' hours',
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            return $result;
        } catch (Exception $e) {
            throw new Exception("Trend analysis error: " . $e->getMessage());
        }
    }
    
    public function getAnomalyDetection($hours = 24) {
        try {
            $data = $this->getRecentData($hours);
            
            if (empty($data)) {
                throw new Exception("No data available for anomaly detection");
            }
            
            // Get AI analysis
            $aiAnalysis = $this->gemini->analyzeSensorData($data, 'anomaly');
            
            // Calculate statistical anomalies
            $anomalies = $this->detectStatisticalAnomalies($data);
            
            $result = [
                'ai_analysis' => $this->parseAIResponse($aiAnalysis),
                'statistical_anomalies' => $anomalies,
                'data_points' => count($data),
                'time_range' => $hours . ' hours',
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            return $result;
        } catch (Exception $e) {
            throw new Exception("Anomaly detection error: " . $e->getMessage());
        }
    }
    
    public function getPredictions($hours = 24) {
        try {
            $data = $this->getRecentData($hours);
            
            if (empty($data)) {
                throw new Exception("No data available for predictions");
            }
            
            // Get AI predictions
            $aiPredictions = $this->gemini->analyzeSensorData($data, 'prediction');
            
            // Calculate statistical predictions (simple linear regression)
            $statisticalPredictions = $this->calculatePredictions($data);
            
            $result = [
                'ai_predictions' => $this->parseAIResponse($aiPredictions),
                'statistical_predictions' => $statisticalPredictions,
                'based_on_points' => count($data),
                'prediction_horizon' => '24-48 hours',
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            return $result;
        } catch (Exception $e) {
            throw new Exception("Prediction error: " . $e->getMessage());
        }
    }
    
    public function getSummaryReport($hours = 24) {
        try {
            $data = $this->getRecentData($hours);
            
            if (empty($data)) {
                throw new Exception("No data available for summary");
            }
            
            // Get AI summary
            $aiSummary = $this->gemini->analyzeSensorData($data, 'summary');
            
            // Calculate summary statistics
            $summary = $this->calculateSummaryStats($data);
            
            $result = [
                'ai_summary' => $this->parseAIResponse($aiSummary),
                'summary_statistics' => $summary,
                'data_points' => count($data),
                'time_range' => $hours . ' hours',
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            return $result;
        } catch (Exception $e) {
            throw new Exception("Summary report error: " . $e->getMessage());
        }
    }
    
    private function calculateStatistics($data) {
        $metrics = ['air_temperature', 'humidity', 'water_level', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3'];
        $stats = [];
        
        foreach ($metrics as $metric) {
            $values = array_column($data, $metric);
            $values = array_filter($values, function($v) { return $v !== null && $v !== ''; });
            
            if (!empty($values)) {
                $stats[$metric] = [
                    'min' => min($values),
                    'max' => max($values),
                    'avg' => round(array_sum($values) / count($values), 2),
                    'trend' => $this->calculateTrend($values),
                    'stddev' => $this->calculateStdDev($values)
                ];
            }
        }
        
        return $stats;
    }
    
    private function detectStatisticalAnomalies($data) {
        $metrics = ['air_temperature', 'humidity', 'water_level', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3'];
        $anomalies = [];
        
        foreach ($metrics as $metric) {
            $values = array_column($data, $metric);
            $values = array_filter($values, function($v) { return $v !== null && $v !== ''; });
            
            if (count($values) < 3) continue;
            
            $mean = array_sum($values) / count($values);
            $stddev = $this->calculateStdDev($values);
            $threshold = 2 * $stddev; // 2 standard deviations
            
            foreach ($data as $index => $reading) {
                $value = floatval($reading[$metric]);
                if (abs($value - $mean) > $threshold) {
                    $anomalies[] = [
                        'metric' => $metric,
                        'value' => $value,
                        'expected_range' => [$mean - $threshold, $mean + $threshold],
                        'timestamp' => $reading['timestamp'],
                        'severity' => abs($value - $mean) > (3 * $stddev) ? 'high' : 'medium'
                    ];
                }
            }
        }
        
        return $anomalies;
    }
    
    private function calculatePredictions($data) {
        $metrics = ['air_temperature', 'humidity', 'water_level', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3'];
        $predictions = [];
        
        foreach ($metrics as $metric) {
            $values = array_column($data, $metric);
            $values = array_filter($values, function($v) { return $v !== null && $v !== ''; });
            
            if (count($values) < 3) continue;
            
            // Simple linear regression for trend
            $trend = $this->calculateTrend($values);
            $lastValue = end($values);
            
            // Predict next 3 time periods (assuming hourly data)
            $predictions[$metric] = [
                'next_1h' => round($lastValue + $trend, 2),
                'next_6h' => round($lastValue + ($trend * 6), 2),
                'next_24h' => round($lastValue + ($trend * 24), 2),
                'confidence' => $this->calculatePredictionConfidence($values),
                'trend_direction' => $trend > 0.1 ? 'increasing' : ($trend < -0.1 ? 'decreasing' : 'stable')
            ];
        }
        
        return $predictions;
    }
    
    private function calculateSummaryStats($data) {
        $latest = $data[0]; // Most recent reading
        $earliest = end($data);
        
        return [
            'latest_reading' => $latest,
            'time_span' => [
                'from' => $earliest['timestamp'],
                'to' => $latest['timestamp']
            ],
            'data_quality' => $this->assessDataQuality($data),
            'environmental_status' => $this->assessEnvironmentalStatus($latest),
            'key_changes' => $this->identifyKeyChanges($data)
        ];
    }
    
    private function calculateTrend($values) {
        $n = count($values);
        if ($n < 2) return 0;
        
        $x = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $values[$i];
            $sumX2 += $x[$i] * $x[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        return round($slope, 4);
    }
    
    private function calculateStdDev($values) {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);
        
        return sqrt($variance);
    }
    
    private function calculatePredictionConfidence($values) {
        // Simple confidence based on data consistency
        $stddev = $this->calculateStdDev($values);
        $mean = array_sum($values) / count($values);
        $cv = $stddev / $mean; // Coefficient of variation
        
        if ($cv < 0.1) return 'high';
        if ($cv < 0.25) return 'medium';
        return 'low';
    }
    
    private function assessDataQuality($data) {
        $totalPoints = count($data);
        $completePoints = 0;
        
        foreach ($data as $reading) {
            $complete = true;
            foreach (['air_temperature', 'humidity', 'water_level', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3'] as $field) {
                if ($reading[$field] === null || $reading[$field] === '') {
                    $complete = false;
                    break;
                }
            }
            if ($complete) $completePoints++;
        }
        
        $quality = ($completePoints / $totalPoints) * 100;
        
        return [
            'completeness' => round($quality, 1) . '%',
            'status' => $quality >= 90 ? 'excellent' : ($quality >= 75 ? 'good' : 'needs_attention')
        ];
    }
    
    private function assessEnvironmentalStatus($latest) {
        $status = 'normal';
        $alerts = [];
        
        // Temperature checks
        $airTemp = floatval($latest['air_temperature']);
        if ($airTemp < 20 || $airTemp > 35) {
            $status = 'warning';
            $alerts[] = 'Air temperature outside normal range';
        }
        
        // Humidity checks
        $humidity = floatval($latest['humidity']);
        if ($humidity < 30 || $humidity > 90) {
            $status = 'warning';
            $alerts[] = 'Humidity levels concerning';
        }
        
        // Water level checks
        $waterLevel = floatval($latest['water_level']);
        if ($waterLevel < 1.5 || $waterLevel > 3.5) {
            $status = 'critical';
            $alerts[] = 'Water level critical';
        }
        
        return [
            'status' => $status,
            'alerts' => $alerts
        ];
    }
    
    private function identifyKeyChanges($data) {
        if (count($data) < 2) return [];
        
        $latest = $data[0];
        $previous = $data[1];
        $changes = [];
        
        $metrics = ['air_temperature', 'humidity', 'water_level', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3'];
        
        foreach ($metrics as $metric) {
            $current = floatval($latest[$metric]);
            $prev = floatval($previous[$metric]);
            $change = $current - $prev;
            
            if (abs($change) > 0.1) { // Significant change threshold
                $changes[$metric] = [
                    'change' => round($change, 2),
                    'percentage' => $prev != 0 ? round(($change / $prev) * 100, 1) : 0,
                    'direction' => $change > 0 ? 'increase' : 'decrease'
                ];
            }
        }
        
        return $changes;
    }
    
    private function parseAIResponse($response) {
        // Try to parse as JSON first
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        // If not JSON, return as text with basic parsing
        return [
            'raw_text' => $response,
            'parsed' => $this->extractInsights($response)
        ];
    }
    
    private function extractInsights($text) {
        // Basic text parsing to extract key insights
        $insights = [];
        
        // Look for common patterns in AI responses
        if (preg_match_all('/(\d+\.?\d*)\s*(°C|%|m)\s*(increase|decrease|stable)/i', $text, $matches)) {
            $insights['detected_changes'] = array_combine($matches[0], $matches[3]);
        }
        
        // Extract recommendations
        if (preg_match('/recommend[^.]*\.?/i', $text, $matches)) {
            $insights['recommendations'] = $matches[0];
        }
        
        return $insights;
    }
}
?>