<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/services/AnalysisService.php';

try {
    $hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;
    
    if ($hours < 1 || $hours > 168) { // Max 1 week
        throw new Exception('Hours parameter must be between 1 and 168');
    }
    
    $analysisService = new AnalysisService();
    $summary = $analysisService->getSummaryReport($hours);
    
    echo json_encode([
        'status' => 'success',
        'data' => $summary
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>