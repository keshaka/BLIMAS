<?php
require_once __DIR__ . '/../config/admin-auth.php';
require_once __DIR__ . '/../config/database.php';

// Check admin authentication
$auth = new AdminAuth();
$auth->requireAdmin();

// Get parameters
$format = $_GET['format'] ?? 'csv';
$limit = (int)($_GET['limit'] ?? 1000);
$days = (int)($_GET['days'] ?? 30);

// Validate format
$allowed_formats = ['csv', 'json', 'excel'];
if (!in_array($format, $allowed_formats)) {
    $format = 'csv';
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Fetch data
    $sql = "SELECT 
                id,
                timestamp,
                COALESCE(air_temperature, air_temp) as air_temperature,
                humidity,
                water_level,
                COALESCE(water_temp_depth1, water_temp1) as water_temp_depth1,
                COALESCE(water_temp_depth2, water_temp2) as water_temp_depth2,
                COALESCE(water_temp_depth3, water_temp3) as water_temp_depth3,
                battery_level
            FROM sensor_data 
            WHERE timestamp > DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY timestamp DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$days, $limit]);
    $data = $stmt->fetchAll();
    
    // Generate filename
    $filename = 'blimas_sensor_data_' . date('Y-m-d_H-i-s');
    
    switch ($format) {
        case 'csv':
            exportCSV($data, $filename);
            break;
        case 'json':
            exportJSON($data, $filename);
            break;
        case 'excel':
            exportExcel($data, $filename);
            break;
    }
    
} catch (Exception $e) {
    error_log("Export error: " . $e->getMessage());
    http_response_code(500);
    echo "Export failed: " . htmlspecialchars($e->getMessage());
}

function exportCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    
    $output = fopen('php://output', 'w');
    
    // Write header
    fputcsv($output, [
        'ID',
        'Timestamp',
        'Air Temperature (°C)',
        'Humidity (%)',
        'Water Level (cm)',
        'Water Temp Depth 1 (°C)',
        'Water Temp Depth 2 (°C)',
        'Water Temp Depth 3 (°C)',
        'Battery Level (%)'
    ]);
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['timestamp'],
            $row['air_temperature'],
            $row['humidity'],
            $row['water_level'],
            $row['water_temp_depth1'],
            $row['water_temp_depth2'],
            $row['water_temp_depth3'],
            $row['battery_level']
        ]);
    }
    
    fclose($output);
}

function exportJSON($data, $filename) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    
    $export_data = [
        'export_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_records' => count($data),
            'format' => 'BLIMAS Sensor Data Export'
        ],
        'data' => []
    ];
    
    foreach ($data as $row) {
        $export_data['data'][] = [
            'id' => (int)$row['id'],
            'timestamp' => $row['timestamp'],
            'air_temperature' => $row['air_temperature'] ? (float)$row['air_temperature'] : null,
            'humidity' => $row['humidity'] ? (float)$row['humidity'] : null,
            'water_level' => $row['water_level'] ? (float)$row['water_level'] : null,
            'water_temperatures' => [
                'depth1' => $row['water_temp_depth1'] ? (float)$row['water_temp_depth1'] : null,
                'depth2' => $row['water_temp_depth2'] ? (float)$row['water_temp_depth2'] : null,
                'depth3' => $row['water_temp_depth3'] ? (float)$row['water_temp_depth3'] : null
            ],
            'battery_level' => $row['battery_level'] ? (float)$row['battery_level'] : null
        ];
    }
    
    echo json_encode($export_data, JSON_PRETTY_PRINT);
}

function exportExcel($data, $filename) {
    // For Excel export, we'll create an XML format that Excel can read
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    
    echo "<?xml version=\"1.0\"?>\n";
    echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\">\n";
    echo "<Worksheet ss:Name=\"BLIMAS Sensor Data\">\n";
    echo "<Table>\n";
    
    // Header row
    echo "<Row>\n";
    $headers = [
        'ID', 'Timestamp', 'Air Temperature (°C)', 'Humidity (%)', 
        'Water Level (cm)', 'Water Temp Depth 1 (°C)', 
        'Water Temp Depth 2 (°C)', 'Water Temp Depth 3 (°C)', 
        'Battery Level (%)'
    ];
    foreach ($headers as $header) {
        echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($header) . "</Data></Cell>\n";
    }
    echo "</Row>\n";
    
    // Data rows
    foreach ($data as $row) {
        echo "<Row>\n";
        echo "<Cell><Data ss:Type=\"Number\">" . $row['id'] . "</Data></Cell>\n";
        echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['timestamp']) . "</Data></Cell>\n";
        echo "<Cell><Data ss:Type=\"Number\">" . ($row['air_temperature'] ?? '') . "</Data></Cell>\n";
        echo "<Cell><Data ss:Type=\"Number\">" . ($row['humidity'] ?? '') . "</Data></Cell>\n";
        echo "<Cell><Data ss:Type=\"Number\">" . ($row['water_level'] ?? '') . "</Data></Cell>\n";
        echo "<Cell><Data ss:Type=\"Number\">" . ($row['water_temp_depth1'] ?? '') . "</Data></Cell>\n";
        echo "<Cell><Data ss:Type=\"Number\">" . ($row['water_temp_depth2'] ?? '') . "</Data></Cell>\n";
        echo "<Cell><Data ss:Type=\"Number\">" . ($row['water_temp_depth3'] ?? '') . "</Data></Cell>\n";
        echo "<Cell><Data ss:Type=\"Number\">" . ($row['battery_level'] ?? '') . "</Data></Cell>\n";
        echo "</Row>\n";
    }
    
    echo "</Table>\n";
    echo "</Worksheet>\n";
    echo "</Workbook>\n";
}
?>