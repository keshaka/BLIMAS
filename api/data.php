<?php
/**
 * BLIMAS Real-time Data API
 * Provides JSON endpoints for AJAX updates
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../includes/database.php';
require_once '../includes/weather.php';

class DataAPI {
    
    /**
     * Get latest sensor data
     */
    public static function getLatestSensorData() {
        try {
            $conn = DatabaseConfig::getMySQLiConnection();
            $sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $conn->close();
                
                // Format the response
                return [
                    'success' => true,
                    'data' => [
                        'air_temperature' => floatval($data['air_temperature']),
                        'humidity' => floatval($data['humidity']),
                        'water_level' => floatval($data['water_level']),
                        'water_temp1' => floatval($data['water_temp1']),
                        'water_temp2' => floatval($data['water_temp2']),
                        'water_temp3' => floatval($data['water_temp3']),
                        'battery_level' => $data['battery_level'] ? floatval($data['battery_level']) : null,
                        'timestamp' => $data['timestamp'],
                        'last_updated' => date('Y-m-d H:i:s')
                    ]
                ];
            } else {
                $conn->close();
                return [
                    'success' => false,
                    'error' => 'No sensor data found'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get weather data
     */
    public static function getWeatherData() {
        try {
            $weather = new WeatherAPI();
            $data = $weather->getWeatherData();
            
            if ($data) {
                return [
                    'success' => true,
                    'data' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to fetch weather data'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Weather API error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get historical data for charts
     */
    public static function getHistoricalData($parameter, $limit = 50) {
        $validParameters = ['air_temperature', 'humidity', 'water_level', 'water_temp1', 'water_temp2', 'water_temp3'];
        
        if (!in_array($parameter, $validParameters)) {
            return [
                'success' => false,
                'error' => 'Invalid parameter'
            ];
        }
        
        try {
            $conn = DatabaseConfig::getMySQLiConnection();
            $stmt = $conn->prepare(
                "SELECT {$parameter} as value, timestamp 
                 FROM sensor_data 
                 ORDER BY timestamp DESC 
                 LIMIT ?"
            );
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    'value' => floatval($row['value']),
                    'timestamp' => $row['timestamp']
                ];
            }
            
            $conn->close();
            
            // Reverse to get chronological order
            $data = array_reverse($data);
            
            return [
                'success' => true,
                'data' => $data,
                'parameter' => $parameter
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get multiple parameters for water temperature chart
     */
    public static function getWaterTemperatureData($limit = 50) {
        try {
            $conn = DatabaseConfig::getMySQLiConnection();
            $stmt = $conn->prepare(
                "SELECT water_temp1, water_temp2, water_temp3, timestamp 
                 FROM sensor_data 
                 ORDER BY timestamp DESC 
                 LIMIT ?"
            );
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    'temp1' => floatval($row['water_temp1']),
                    'temp2' => floatval($row['water_temp2']),
                    'temp3' => floatval($row['water_temp3']),
                    'timestamp' => $row['timestamp']
                ];
            }
            
            $conn->close();
            
            // Reverse to get chronological order
            $data = array_reverse($data);
            
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get system status
     */
    public static function getSystemStatus() {
        try {
            $conn = DatabaseConfig::getMySQLiConnection();
            
            // Get latest data timestamp
            $result = $conn->query("SELECT timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 1");
            $latestData = $result->fetch_assoc();
            
            // Get total records
            $result = $conn->query("SELECT COUNT(*) as total FROM sensor_data");
            $totalRecords = $result->fetch_assoc();
            
            $conn->close();
            
            $lastUpdate = $latestData ? $latestData['timestamp'] : null;
            $timeSinceUpdate = $lastUpdate ? time() - strtotime($lastUpdate) : null;
            
            return [
                'success' => true,
                'data' => [
                    'last_update' => $lastUpdate,
                    'seconds_since_update' => $timeSinceUpdate,
                    'total_records' => intval($totalRecords['total']),
                    'status' => $timeSinceUpdate && $timeSinceUpdate < 600 ? 'online' : 'offline', // Online if updated within 10 minutes
                    'database_connected' => true
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'data' => [
                    'database_connected' => false,
                    'status' => 'error'
                ]
            ];
        }
    }
}

// Handle API requests
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'latest':
        echo json_encode(DataAPI::getLatestSensorData());
        break;
        
    case 'weather':
        echo json_encode(DataAPI::getWeatherData());
        break;
        
    case 'historical':
        $parameter = $_GET['parameter'] ?? '';
        $limit = intval($_GET['limit'] ?? 50);
        echo json_encode(DataAPI::getHistoricalData($parameter, $limit));
        break;
        
    case 'water_temperature':
        $limit = intval($_GET['limit'] ?? 50);
        echo json_encode(DataAPI::getWaterTemperatureData($limit));
        break;
        
    case 'status':
        echo json_encode(DataAPI::getSystemStatus());
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action parameter',
            'available_actions' => ['latest', 'weather', 'historical', 'water_temperature', 'status']
        ]);
        break;
}
?>