<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/database.php';
include_once '../config/gemini.php';

$database = new Database();
$db = $database->getConnection();
$gemini = new GeminiAPI();

$analysisType = isset($_GET['type']) ? $_GET['type'] : 'general';
$hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;

try {
    // Get historical data for analysis
    $query = "SELECT * FROM sensor_data WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR) ORDER BY timestamp ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $hours);
    $stmt->execute();
    
    $sensorData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($sensorData)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No sensor data available for analysis'
        ]);
        exit;
    }
    
    // Get AI analysis from Gemini
    $aiAnalysis = $gemini->analyzeSensorData($sensorData, $analysisType);
    
    // Calculate statistical insights
    $statistics = calculateStatistics($sensorData);
    
    // Detect anomalies
    $anomalies = detectAnomalies($sensorData);
    
    // Generate trend predictions
    $predictions = generatePredictions($sensorData);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'ai_analysis' => $aiAnalysis,
            'statistics' => $statistics,
            'anomalies' => $anomalies,
            'predictions' => $predictions,
            'data_points' => count($sensorData),
            'time_range' => $hours
        ]
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function calculateStatistics($data) {
    if (empty($data)) return [];
    
    $fields = [
        'air_temperature' => 'Air Temperature (°C)',
        'humidity' => 'Humidity (%)',
        'water_level' => 'Water Level (m)',
        'water_temp_depth1' => 'Water Temp Surface (°C)',
        'water_temp_depth2' => 'Water Temp Middle (°C)',
        'water_temp_depth3' => 'Water Temp Bottom (°C)'
    ];
    
    $stats = [];
    
    foreach ($fields as $field => $label) {
        $values = array_filter(array_column($data, $field), function($val) {
            return $val !== null && is_numeric($val);
        });
        
        if (!empty($values)) {
            $avg = array_sum($values) / count($values);
            $min = min($values);
            $max = max($values);
            
            // Calculate standard deviation
            $variance = array_sum(array_map(function($val) use ($avg) {
                return pow($val - $avg, 2);
            }, $values)) / count($values);
            $stdDev = sqrt($variance);
            
            $stats[$field] = [
                'label' => $label,
                'average' => round($avg, 2),
                'minimum' => round($min, 2),
                'maximum' => round($max, 2),
                'std_deviation' => round($stdDev, 2),
                'range' => round($max - $min, 2),
                'trend' => calculateTrend($values)
            ];
        }
    }
    
    return $stats;
}

function calculateTrend($values) {
    if (count($values) < 2) return 'insufficient_data';
    
    $n = count($values);
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
    
    if (abs($slope) < 0.01) return 'stable';
    return $slope > 0 ? 'increasing' : 'decreasing';
}

function detectAnomalies($data) {
    if (empty($data)) return [];
    
    $anomalies = [];
    $fields = ['air_temperature', 'humidity', 'water_level', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3'];
    
    foreach ($fields as $field) {
        $values = array_filter(array_column($data, $field), function($val) {
            return $val !== null && is_numeric($val);
        });
        
        if (count($values) < 3) continue;
        
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($val) use ($mean) {
            return pow($val - $mean, 2);
        }, $values)) / count($values);
        $stdDev = sqrt($variance);
        
        // Define thresholds based on standard deviations
        $upperThreshold = $mean + (2 * $stdDev);
        $lowerThreshold = $mean - (2 * $stdDev);
        
        $fieldAnomalies = [];
        foreach ($data as $i => $record) {
            $value = $record[$field];
            if ($value !== null && is_numeric($value)) {
                if ($value > $upperThreshold || $value < $lowerThreshold) {
                    $fieldAnomalies[] = [
                        'timestamp' => $record['timestamp'],
                        'value' => $value,
                        'expected_range' => [$lowerThreshold, $upperThreshold],
                        'severity' => abs($value - $mean) > (3 * $stdDev) ? 'high' : 'medium'
                    ];
                }
            }
        }
        
        if (!empty($fieldAnomalies)) {
            $anomalies[$field] = $fieldAnomalies;
        }
    }
    
    return $anomalies;
}

function generatePredictions($data) {
    if (count($data) < 5) {
        return ['error' => 'Insufficient data for predictions'];
    }
    
    $predictions = [];
    $fields = ['air_temperature', 'humidity', 'water_level'];
    
    foreach ($fields as $field) {
        $values = array_filter(array_column($data, $field), function($val) {
            return $val !== null && is_numeric($val);
        });
        
        if (count($values) < 5) continue;
        
        // Simple linear regression prediction
        $n = count($values);
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
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Predict next 6 hours
        $futureValues = [];
        for ($i = 1; $i <= 6; $i++) {
            $futureX = $n + $i;
            $predictedValue = $slope * $futureX + $intercept;
            $futureValues[] = [
                'hour' => $i,
                'predicted_value' => round($predictedValue, 2)
            ];
        }
        
        $predictions[$field] = [
            'trend' => $slope > 0 ? 'increasing' : ($slope < 0 ? 'decreasing' : 'stable'),
            'confidence' => calculatePredictionConfidence($values, $slope, $intercept),
            'future_values' => $futureValues
        ];
    }
    
    return $predictions;
}

function calculatePredictionConfidence($values, $slope, $intercept) {
    $n = count($values);
    $x = range(1, $n);
    
    $totalError = 0;
    for ($i = 0; $i < $n; $i++) {
        $predicted = $slope * $x[$i] + $intercept;
        $error = abs($values[$i] - $predicted);
        $totalError += $error;
    }
    
    $meanError = $totalError / $n;
    $meanValue = array_sum($values) / $n;
    
    // Calculate confidence as percentage (inverse of relative error)
    $relativeError = $meanError / $meanValue;
    $confidence = max(0, min(100, (1 - $relativeError) * 100));
    
    return round($confidence, 1);
}
?>