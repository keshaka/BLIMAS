<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Error logging function
function logError($message) {
    error_log("[BLIMAS Analysis] " . date('Y-m-d H:i:s') . " - " . $message);
}

try {
    include_once '../config/database.php';
    include_once '../config/gemini.php';

    $database = new Database();
    $db = $database->getConnection();
    $gemini = new GeminiAPI();

    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Get analysis parameters
    $analysisType = $_GET['type'] ?? 'comprehensive';
    $period = $_GET['period'] ?? 'week';
    
    logError("Analysis request: type=$analysisType, period=$period");

    // Validate parameters
    $validTypes = ['comprehensive', 'water_quality', 'climate_impact', 'ecosystem_health', 'alerts'];
    $validPeriods = ['day', 'week', 'month'];
    
    if (!in_array($analysisType, $validTypes)) {
        throw new Exception('Invalid analysis type');
    }
    
    if (!in_array($period, $validPeriods)) {
        throw new Exception('Invalid time period');
    }

    // Fetch data for analysis
    $whereClause = '';
    switch($period) {
        case 'day':
            $whereClause = "WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            break;
        case 'week':
            $whereClause = "WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $whereClause = "WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }

    // Get statistical data
    $query = "SELECT 
        COUNT(*) as total_readings,
        AVG(air_temperature) as avg_air_temp,
        MIN(air_temperature) as min_air_temp,
        MAX(air_temperature) as max_air_temp,
        AVG(humidity) as avg_humidity,
        MIN(humidity) as min_humidity,
        MAX(humidity) as max_humidity,
        AVG(water_level) as avg_water_level,
        MIN(water_level) as min_water_level,
        MAX(water_level) as max_water_level,
        AVG(water_temp_depth1) as avg_surface_temp,
        AVG(water_temp_depth2) as avg_mid_temp,
        AVG(water_temp_depth3) as avg_bottom_temp,
        MIN(water_temp_depth1) as min_surface_temp,
        MAX(water_temp_depth1) as max_surface_temp,
        STDDEV(air_temperature) as air_temp_stddev,
        STDDEV(water_level) as water_level_stddev
    FROM sensor_data $whereClause";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stats || $stats['total_readings'] == 0) {
        throw new Exception('No data available for the selected period');
    }

    logError("Data retrieved: {$stats['total_readings']} readings");

    // Get recent readings for trend analysis
    $trendQuery = "SELECT * FROM sensor_data $whereClause ORDER BY timestamp DESC LIMIT 10";
    $trendStmt = $db->prepare($trendQuery);
    $trendStmt->execute();
    $recentReadings = $trendStmt->fetchAll(PDO::FETCH_ASSOC);

    // Create analysis prompt
    $prompt = createAnalysisPrompt($analysisType, $stats, $recentReadings, $period);
    
    logError("Generating AI analysis...");

    // Generate AI analysis
    $analysis = $gemini->generateAnalysis($prompt);

    if (isset($analysis['error'])) {
        logError("Gemini API error: " . $analysis['error']);
        // Don't throw error, let it fall through to return the mock analysis
    }

    // Prepare response
    $response = [
        'success' => true,
        'analysis' => $analysis['analysis'] ?? 'Analysis generation failed',
        'stats' => $stats,
        'period' => $period,
        'type' => $analysisType,
        'generated_at' => date('Y-m-d H:i:s'),
        'method' => $analysis['method'] ?? 'unknown'
    ];

    // Add note if using mock data
    if (isset($analysis['note'])) {
        $response['note'] = $analysis['note'];
    }

    logError("Analysis generated successfully using method: " . ($analysis['method'] ?? 'unknown'));

    echo json_encode($response);

} catch (Exception $e) {
    logError("Analysis generation failed: " . $e->getMessage());
    
    echo json_encode([
        'error' => 'Analysis generation failed: ' . $e->getMessage(),
        'details' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'user' => 'keshaka',
            'requested_type' => $_GET['type'] ?? 'unknown',
            'requested_period' => $_GET['period'] ?? 'unknown'
        ]
    ]);
}

function createAnalysisPrompt($type, $stats, $recentReadings, $period) {
    $periodLabel = ucfirst($period);
    
    $basePrompt = "You are an environmental scientist analyzing water quality and atmospheric data from Bolgoda Lake in Sri Lanka. ";
    
    $dataContext = "Over the last $periodLabel, we have collected {$stats['total_readings']} sensor readings with the following statistics:\n\n";
    
    $dataContext .= "AIR TEMPERATURE:\n";
    $dataContext .= "- Average: " . round(floatval($stats['avg_air_temp']), 2) . "°C\n";
    $dataContext .= "- Range: " . round(floatval($stats['min_air_temp']), 2) . "°C to " . round(floatval($stats['max_air_temp']), 2) . "°C\n\n";
    
    $dataContext .= "HUMIDITY:\n";
    $dataContext .= "- Average: " . round(floatval($stats['avg_humidity']), 2) . "%\n";
    $dataContext .= "- Range: " . round(floatval($stats['min_humidity']), 2) . "% to " . round(floatval($stats['max_humidity']), 2) . "%\n\n";
    
    $dataContext .= "WATER LEVEL:\n";
    $dataContext .= "- Average: " . round(floatval($stats['avg_water_level']), 2) . "m\n";
    $dataContext .= "- Range: " . round(floatval($stats['min_water_level']), 2) . "m to " . round(floatval($stats['max_water_level']), 2) . "m\n\n";
    
    $dataContext .= "WATER TEMPERATURE (by depth):\n";
    $dataContext .= "- Surface: " . round(floatval($stats['avg_surface_temp']), 2) . "°C\n";
    $dataContext .= "- Mid-depth: " . round(floatval($stats['avg_mid_temp']), 2) . "°C\n";
    $dataContext .= "- Bottom: " . round(floatval($stats['avg_bottom_temp']), 2) . "°C\n\n";
    
    switch ($type) {
        case 'water_quality':
            return $basePrompt . $dataContext . 
                "Focus your analysis on WATER QUALITY aspects. Analyze:\n" .
                "1. Water temperature stratification and its ecological implications\n" .
                "2. The relationship between air temperature and water temperature\n" .
                "3. Water level changes and their potential causes\n" .
                "4. Overall water quality indicators and trends\n" .
                "5. Recommendations for water quality management\n\n" .
                "Provide specific, actionable insights in a professional tone suitable for environmental managers.";
                
        case 'climate_impact':
            return $basePrompt . $dataContext . 
                "Focus your analysis on CLIMATE and ATMOSPHERIC conditions. Analyze:\n" .
                "1. Air temperature patterns and seasonal variations\n" .
                "2. Humidity levels and their relationship with local weather\n" .
                "3. Climate impact on lake ecosystem\n" .
                "4. Potential climate change indicators\n" .
                "5. Recommendations for climate adaptation strategies\n\n" .
                "Provide insights about climate trends and their environmental impact.";
                
        case 'ecosystem_health':
            return $basePrompt . $dataContext . 
                "Focus your analysis on ECOSYSTEM HEALTH and biodiversity. Analyze:\n" .
                "1. Environmental conditions suitable for aquatic life\n" .
                "2. Temperature and oxygen stratification effects\n" .
                "3. Water level impacts on shoreline ecosystems\n" .
                "4. Overall ecosystem stability indicators\n" .
                "5. Recommendations for biodiversity conservation\n\n" .
                "Provide ecological insights and conservation recommendations.";
                
        case 'alerts':
            return $basePrompt . $dataContext . 
                "Focus on identifying ENVIRONMENTAL ALERTS and WARNINGS. Analyze:\n" .
                "1. Any parameters outside normal ranges\n" .
                "2. Sudden changes or concerning trends\n" .
                "3. Potential environmental risks\n" .
                "4. Urgent actions needed\n" .
                "5. Monitoring recommendations\n\n" .
                "Highlight any critical issues that require immediate attention.";
                
        default: // comprehensive
            return $basePrompt . $dataContext . 
                "Provide a COMPREHENSIVE ENVIRONMENTAL ANALYSIS covering:\n" .
                "1. Overall environmental conditions assessment\n" .
                "2. Key trends and patterns observed\n" .
                "3. Relationships between different parameters\n" .
                "4. Environmental health indicators\n" .
                "5. Potential concerns or positive developments\n" .
                "6. Actionable recommendations for lake management\n\n" .
                "Structure your response with clear sections and provide both technical insights and practical recommendations.";
    }
}
?>