<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

@include_once '../config/database.php';
@include_once '../config/gemini.php';

// Demo data for testing when database is not available
function generateDemoData($hours = 24) {
    $data = [];
    $baseTime = time() - ($hours * 3600);
    
    for ($i = 0; $i < $hours * 6; $i++) { // Every 10 minutes
        $timestamp = $baseTime + ($i * 600);
        
        // Generate realistic sensor data with some variation
        $timeOfDay = (date('H', $timestamp) + (date('i', $timestamp) / 60)) / 24;
        $tempBase = 28 + (3 * sin($timeOfDay * 2 * M_PI)); // Daily temperature cycle
        
        $data[] = [
            'id' => $i + 1,
            'air_temperature' => round($tempBase + (rand(-15, 15) / 10), 2),
            'humidity' => round(75 + (rand(-10, 10)), 2),
            'water_level' => round(2.5 + (rand(-20, 20) / 100), 2),
            'water_temp_depth1' => round($tempBase - 1 + (rand(-10, 10) / 10), 2),
            'water_temp_depth2' => round($tempBase - 2 + (rand(-10, 10) / 10), 2),
            'water_temp_depth3' => round($tempBase - 3 + (rand(-10, 10) / 10), 2),
            'timestamp' => date('Y-m-d H:i:s', $timestamp)
        ];
    }
    
    return $data;
}

try {
    // Try to connect to database first (suppress error output)
    $database = new Database();
    $db = @$database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    $gemini = new GeminiAPI();
    $analysisType = isset($_GET['type']) ? $_GET['type'] : 'general';
    $hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;

    // Try to get real data
    $query = "SELECT * FROM sensor_data WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR) ORDER BY timestamp ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $hours);
    $stmt->execute();
    
    $sensorData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no real data, use demo data
    if (empty($sensorData)) {
        $sensorData = generateDemoData($hours);
    }

} catch(Exception $e) {
    // Fallback to demo mode
    $analysisType = isset($_GET['type']) ? $_GET['type'] : 'general';
    $hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;
    $sensorData = generateDemoData($hours);
    
    // Create dummy Gemini API for demo
    $gemini = new class {
        public function analyzeSensorData($data, $type) {
            $demoAnalysis = "## Environmental Analysis Summary\n\n";
            $demoAnalysis .= "**Data Overview:** Analyzed " . count($data) . " data points from Bolgoda Lake monitoring sensors.\n\n";
            
            switch($type) {
                case 'trends':
                    $demoAnalysis .= "### Trend Analysis\n";
                    $demoAnalysis .= "- **Temperature Trends:** Air temperature shows normal diurnal variation with peaks around midday\n";
                    $demoAnalysis .= "- **Humidity Patterns:** Humidity levels remain stable within healthy ranges (70-80%)\n";
                    $demoAnalysis .= "- **Water Level:** Consistent water levels indicate stable hydrological conditions\n";
                    $demoAnalysis .= "- **Water Temperature Stratification:** Normal thermal layering observed across depths\n";
                    break;
                    
                case 'predictions':
                    $demoAnalysis .= "### Predictive Insights\n";
                    $demoAnalysis .= "- **Short-term Forecast:** Temperature expected to follow normal diurnal patterns\n";
                    $demoAnalysis .= "- **Humidity Predictions:** Stable humidity levels anticipated\n";
                    $demoAnalysis .= "- **Water Level Trends:** No significant changes predicted in the next 6-12 hours\n";
                    $demoAnalysis .= "- **Environmental Stability:** Overall conditions expected to remain within normal parameters\n";
                    break;
                    
                case 'anomalies':
                    $demoAnalysis .= "### Anomaly Detection\n";
                    $demoAnalysis .= "- **Overall Assessment:** No significant anomalies detected in the current dataset\n";
                    $demoAnalysis .= "- **Temperature Variance:** All readings within expected ranges\n";
                    $demoAnalysis .= "- **Sensor Reliability:** All monitoring systems functioning normally\n";
                    $demoAnalysis .= "- **Environmental Health:** Lake ecosystem indicators show stable conditions\n";
                    break;
                    
                case 'insights':
                    $demoAnalysis .= "### Environmental Insights\n";
                    $demoAnalysis .= "- **Ecosystem Health:** Current readings indicate a healthy lake ecosystem\n";
                    $demoAnalysis .= "- **Water Quality:** Temperature stratification suggests good mixing patterns\n";
                    $demoAnalysis .= "- **Monitoring Recommendations:** Continue regular monitoring for seasonal changes\n";
                    $demoAnalysis .= "- **Conservation Status:** Environmental parameters within acceptable limits\n";
                    break;
                    
                default:
                    $demoAnalysis .= "### Comprehensive Analysis\n";
                    $demoAnalysis .= "**Environmental Conditions:** The Bolgoda Lake monitoring data shows stable environmental conditions with all parameters within normal ranges.\n\n";
                    $demoAnalysis .= "**Key Findings:**\n";
                    $demoAnalysis .= "- Air temperature following expected diurnal patterns\n";
                    $demoAnalysis .= "- Humidity levels stable and within healthy ranges\n";
                    $demoAnalysis .= "- Water level showing consistency\n";
                    $demoAnalysis .= "- Water temperature stratification normal across all depths\n\n";
                    $demoAnalysis .= "**Recommendations:**\n";
                    $demoAnalysis .= "- Continue regular monitoring schedule\n";
                    $demoAnalysis .= "- Monitor for seasonal variations\n";
                    $demoAnalysis .= "- Maintain current conservation practices\n";
            }
            
            $demoAnalysis .= "\n\n*Note: This analysis is generated using demo data for testing purposes.*";
            
            return ['error' => false, 'analysis' => $demoAnalysis];
        }
    };
}

// Calculate statistical insights
$statistics = calculateStatistics($sensorData);

// Detect anomalies
$anomalies = detectAnomalies($sensorData);

// Generate trend predictions
$predictions = generatePredictions($sensorData);

// Get AI analysis
$aiAnalysis = $gemini->analyzeSensorData($sensorData, $analysisType);

echo json_encode([
    'status' => 'success',
    'data' => [
        'ai_analysis' => $aiAnalysis,
        'statistics' => $statistics,
        'anomalies' => $anomalies,
        'predictions' => $predictions,
        'data_points' => count($sensorData),
        'time_range' => $hours,
        'demo_mode' => !isset($db) || !$db
    ]
]);

// Copy the functions from the original get_analysis.php
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
                        'expected_range' => [round($lowerThreshold, 2), round($upperThreshold, 2)],
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