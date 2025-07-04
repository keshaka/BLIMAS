<?php
/**
 * Data Export API
 * BLIMAS - Bolgoda Lake Information Monitoring & Analysis System
 */

require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get parameters
$format = $_GET['format'] ?? 'json';
$type = $_GET['type'] ?? 'all';
$limit = (int)($_GET['limit'] ?? 100);
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

try {
    $database = new Database();
    $pdo = $database->connect();
    
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Build query based on type
    $query = "SELECT ";
    switch ($type) {
        case 'temperature':
            $query .= "air_temp, timestamp";
            break;
        case 'humidity':
            $query .= "humidity, timestamp";
            break;
        case 'water_level':
            $query .= "water_level, timestamp";
            break;
        case 'water_temperature':
            $query .= "water_temp1, water_temp2, water_temp3, timestamp";
            break;
        case 'battery':
            $query .= "battery_level, timestamp";
            break;
        default:
            $query .= "*";
    }
    
    $query .= " FROM sensor_data WHERE 1=1";
    
    // Add date filters
    $params = [];
    if ($start_date) {
        $query .= " AND timestamp >= ?";
        $params[] = $start_date;
    }
    if ($end_date) {
        $query .= " AND timestamp <= ?";
        $params[] = $end_date;
    }
    
    $query .= " ORDER BY timestamp DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    // Handle different export formats
    switch ($format) {
        case 'csv':
            exportCSV($data, $type);
            break;
        case 'xml':
            exportXML($data, $type);
            break;
        default:
            exportJSON($data, $type);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Export failed: ' . $e->getMessage()
    ]);
}

function exportJSON($data, $type) {
    echo json_encode([
        'success' => true,
        'data_type' => $type,
        'record_count' => count($data),
        'exported_at' => date('Y-m-d H:i:s'),
        'data' => $data
    ], JSON_PRETTY_PRINT);
}

function exportCSV($data, $type) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="blimas_' . $type . '_' . date('Y-m-d') . '.csv"');
    
    if (empty($data)) {
        echo "No data available\n";
        return;
    }
    
    // Output CSV headers
    $headers = array_keys($data[0]);
    echo implode(',', $headers) . "\n";
    
    // Output data rows
    foreach ($data as $row) {
        echo implode(',', array_map(function($field) {
            return '"' . str_replace('"', '""', $field) . '"';
        }, $row)) . "\n";
    }
}

function exportXML($data, $type) {
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="blimas_' . $type . '_' . date('Y-m-d') . '.xml"');
    
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<blimas_export>' . "\n";
    echo '  <export_info>' . "\n";
    echo '    <data_type>' . htmlspecialchars($type) . '</data_type>' . "\n";
    echo '    <record_count>' . count($data) . '</record_count>' . "\n";
    echo '    <exported_at>' . date('Y-m-d H:i:s') . '</exported_at>' . "\n";
    echo '  </export_info>' . "\n";
    echo '  <data>' . "\n";
    
    foreach ($data as $row) {
        echo '    <record>' . "\n";
        foreach ($row as $key => $value) {
            echo '      <' . htmlspecialchars($key) . '>' . htmlspecialchars($value) . '</' . htmlspecialchars($key) . '>' . "\n";
        }
        echo '    </record>' . "\n";
    }
    
    echo '  </data>' . "\n";
    echo '</blimas_export>' . "\n";
}
?>