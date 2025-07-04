-- BLIMAS Database Schema
-- Bolgoda Lake Information Monitoring & Analysis System

-- Drop existing tables if they exist
DROP TABLE IF EXISTS sensor_data;
DROP TABLE IF EXISTS weather_data;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS system_config;

-- Main sensor data table
CREATE TABLE sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    water_temp1 DECIMAL(5,2) NOT NULL COMMENT 'Water temperature at depth 1',
    water_temp2 DECIMAL(5,2) NOT NULL COMMENT 'Water temperature at depth 2', 
    water_temp3 DECIMAL(5,2) NOT NULL COMMENT 'Water temperature at depth 3',
    air_temp DECIMAL(5,2) NOT NULL COMMENT 'Air temperature from DHT sensor',
    humidity DECIMAL(5,2) NOT NULL COMMENT 'Humidity percentage',
    water_level DECIMAL(8,2) NOT NULL COMMENT 'Water level in cm',
    battery_level DECIMAL(5,2) NOT NULL COMMENT 'Battery level percentage',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp),
    INDEX idx_created_at (created_at)
);

-- Weather data from external API
CREATE TABLE weather_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(100) NOT NULL DEFAULT 'Katubedda,Sri Lanka',
    temperature DECIMAL(5,2) NOT NULL,
    humidity DECIMAL(5,2) NOT NULL,
    condition_text VARCHAR(100),
    wind_speed DECIMAL(5,2),
    wind_direction VARCHAR(10),
    pressure DECIMAL(6,2),
    visibility DECIMAL(5,2),
    uv_index DECIMAL(3,1),
    precipitation DECIMAL(5,2),
    feels_like DECIMAL(5,2),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp)
);

-- Alert system for monitoring critical values
CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('temperature', 'humidity', 'water_level', 'battery', 'system') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    message TEXT NOT NULL,
    value DECIMAL(8,2),
    threshold_value DECIMAL(8,2),
    is_active BOOLEAN DEFAULT TRUE,
    acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_by VARCHAR(100),
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_severity (alert_type, severity),
    INDEX idx_active (is_active),
    INDEX idx_created_at (created_at)
);

-- System configuration table
CREATE TABLE system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default configuration values
INSERT INTO system_config (config_key, config_value, description) VALUES
('data_retention_days', '365', 'Number of days to retain sensor data'),
('alert_email', 'admin@blimas.com', 'Email address for system alerts'),
('maintenance_mode', 'false', 'Enable/disable maintenance mode'),
('chart_data_points', '50', 'Number of data points to show in charts'),
('refresh_interval', '5000', 'Data refresh interval in milliseconds');

-- Create views for easy data access
CREATE VIEW latest_sensor_data AS
SELECT * FROM sensor_data 
ORDER BY timestamp DESC 
LIMIT 1;

CREATE VIEW hourly_averages AS
SELECT 
    DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00') as hour,
    AVG(water_temp1) as avg_water_temp1,
    AVG(water_temp2) as avg_water_temp2,
    AVG(water_temp3) as avg_water_temp3,
    AVG(air_temp) as avg_air_temp,
    AVG(humidity) as avg_humidity,
    AVG(water_level) as avg_water_level,
    AVG(battery_level) as avg_battery_level,
    COUNT(*) as reading_count
FROM sensor_data 
WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY hour
ORDER BY hour DESC;

CREATE VIEW daily_averages AS
SELECT 
    DATE(timestamp) as date,
    AVG(water_temp1) as avg_water_temp1,
    AVG(water_temp2) as avg_water_temp2,
    AVG(water_temp3) as avg_water_temp3,
    AVG(air_temp) as avg_air_temp,
    AVG(humidity) as avg_humidity,
    AVG(water_level) as avg_water_level,
    AVG(battery_level) as avg_battery_level,
    MIN(air_temp) as min_air_temp,
    MAX(air_temp) as max_air_temp,
    COUNT(*) as reading_count
FROM sensor_data 
WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY date
ORDER BY date DESC;

-- Create stored procedures for data management
DELIMITER //

CREATE PROCEDURE CleanOldData(IN retention_days INT)
BEGIN
    DELETE FROM sensor_data 
    WHERE timestamp < DATE_SUB(NOW(), INTERVAL retention_days DAY);
    
    DELETE FROM weather_data 
    WHERE timestamp < DATE_SUB(NOW(), INTERVAL retention_days DAY);
    
    DELETE FROM alerts 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL retention_days DAY) 
    AND acknowledged = TRUE;
END //

CREATE PROCEDURE GetSensorDataRange(
    IN start_date DATETIME,
    IN end_date DATETIME,
    IN limit_count INT
)
BEGIN
    SELECT * FROM sensor_data 
    WHERE timestamp BETWEEN start_date AND end_date
    ORDER BY timestamp DESC
    LIMIT limit_count;
END //

DELIMITER ;