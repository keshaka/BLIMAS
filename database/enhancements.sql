-- BLIMAS Database Enhancements for AI Analysis
-- Phase 2: Database Extensions

USE blimas_db;

-- Add indexes for better performance on time-series queries
CREATE INDEX idx_sensor_data_timestamp ON sensor_data(timestamp);
CREATE INDEX idx_sensor_data_timestamp_desc ON sensor_data(timestamp DESC);

-- Create table for storing analysis results (optional caching)
CREATE TABLE IF NOT EXISTS analysis_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    analysis_type ENUM('trend', 'anomaly', 'prediction', 'summary') NOT NULL,
    time_range_hours INT NOT NULL,
    analysis_data JSON,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    INDEX idx_analysis_type_time (analysis_type, generated_at),
    INDEX idx_expires (expires_at)
);

-- Create table for anomaly detection results
CREATE TABLE IF NOT EXISTS detected_anomalies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_data_id INT,
    metric_name VARCHAR(50) NOT NULL,
    anomaly_value DECIMAL(10,2),
    expected_range_min DECIMAL(10,2),
    expected_range_max DECIMAL(10,2),
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (sensor_data_id) REFERENCES sensor_data(id) ON DELETE CASCADE,
    INDEX idx_detected_anomalies_time (detected_at),
    INDEX idx_detected_anomalies_severity (severity),
    INDEX idx_detected_anomalies_resolved (resolved)
);

-- Create view for latest sensor readings with calculated differences
CREATE OR REPLACE VIEW latest_sensor_analysis AS
SELECT 
    current.*,
    LAG(current.air_temperature) OVER (ORDER BY current.timestamp) as prev_air_temp,
    LAG(current.humidity) OVER (ORDER BY current.timestamp) as prev_humidity,
    LAG(current.water_level) OVER (ORDER BY current.timestamp) as prev_water_level,
    (current.air_temperature - LAG(current.air_temperature) OVER (ORDER BY current.timestamp)) as temp_change,
    (current.humidity - LAG(current.humidity) OVER (ORDER BY current.timestamp)) as humidity_change,
    (current.water_level - LAG(current.water_level) OVER (ORDER BY current.timestamp)) as level_change,
    -- Calculate thermal stratification
    (current.water_temp_depth1 - current.water_temp_depth3) as thermal_stratification
FROM sensor_data current
ORDER BY current.timestamp DESC
LIMIT 10;

-- Stored procedure for data aggregation
DELIMITER //

CREATE PROCEDURE GetSensorStatistics(IN hours_back INT)
BEGIN
    SELECT 
        -- Air Temperature Stats
        MIN(air_temperature) as min_air_temp,
        MAX(air_temperature) as max_air_temp,
        ROUND(AVG(air_temperature), 2) as avg_air_temp,
        ROUND(STDDEV(air_temperature), 2) as stddev_air_temp,
        
        -- Humidity Stats
        MIN(humidity) as min_humidity,
        MAX(humidity) as max_humidity,
        ROUND(AVG(humidity), 2) as avg_humidity,
        ROUND(STDDEV(humidity), 2) as stddev_humidity,
        
        -- Water Level Stats
        MIN(water_level) as min_water_level,
        MAX(water_level) as max_water_level,
        ROUND(AVG(water_level), 2) as avg_water_level,
        ROUND(STDDEV(water_level), 2) as stddev_water_level,
        
        -- Water Temperature Stats by Depth
        MIN(water_temp_depth1) as min_water_temp_d1,
        MAX(water_temp_depth1) as max_water_temp_d1,
        ROUND(AVG(water_temp_depth1), 2) as avg_water_temp_d1,
        
        MIN(water_temp_depth2) as min_water_temp_d2,
        MAX(water_temp_depth2) as max_water_temp_d2,
        ROUND(AVG(water_temp_depth2), 2) as avg_water_temp_d2,
        
        MIN(water_temp_depth3) as min_water_temp_d3,
        MAX(water_temp_depth3) as max_water_temp_d3,
        ROUND(AVG(water_temp_depth3), 2) as avg_water_temp_d3,
        
        -- Data Quality
        COUNT(*) as total_readings,
        COUNT(DISTINCT DATE(timestamp)) as days_covered,
        
        -- Time Range
        MIN(timestamp) as earliest_reading,
        MAX(timestamp) as latest_reading
        
    FROM sensor_data 
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL hours_back HOUR);
END //

DELIMITER ;

-- Stored procedure for anomaly detection
DELIMITER //

CREATE PROCEDURE DetectAnomalies(IN hours_back INT, IN std_dev_threshold DECIMAL(3,1))
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE sensor_id INT;
    DECLARE air_temp, humidity, water_level DECIMAL(10,2);
    DECLARE temp_mean, temp_std, hum_mean, hum_std, level_mean, level_std DECIMAL(10,2);
    DECLARE reading_timestamp TIMESTAMP;
    
    -- Cursor for recent readings
    DECLARE reading_cursor CURSOR FOR 
        SELECT id, air_temperature, humidity, water_level, timestamp
        FROM sensor_data 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL hours_back HOUR)
        ORDER BY timestamp DESC;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Calculate statistics for the time period
    SELECT 
        AVG(air_temperature), STDDEV(air_temperature),
        AVG(humidity), STDDEV(humidity),
        AVG(water_level), STDDEV(water_level)
    INTO temp_mean, temp_std, hum_mean, hum_std, level_mean, level_std
    FROM sensor_data 
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL hours_back HOUR);
    
    -- Clear existing unresolved anomalies for this time period
    UPDATE detected_anomalies 
    SET resolved = TRUE 
    WHERE detected_at >= DATE_SUB(NOW(), INTERVAL hours_back HOUR) 
    AND resolved = FALSE;
    
    -- Check each reading for anomalies
    OPEN reading_cursor;
    
    read_loop: LOOP
        FETCH reading_cursor INTO sensor_id, air_temp, humidity, water_level, reading_timestamp;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Check air temperature anomaly
        IF ABS(air_temp - temp_mean) > (std_dev_threshold * temp_std) THEN
            INSERT INTO detected_anomalies (sensor_data_id, metric_name, anomaly_value, expected_range_min, expected_range_max, severity)
            VALUES (sensor_id, 'air_temperature', air_temp, 
                   temp_mean - (std_dev_threshold * temp_std), 
                   temp_mean + (std_dev_threshold * temp_std),
                   CASE 
                       WHEN ABS(air_temp - temp_mean) > (3 * temp_std) THEN 'critical'
                       WHEN ABS(air_temp - temp_mean) > (2.5 * temp_std) THEN 'high'
                       ELSE 'medium'
                   END);
        END IF;
        
        -- Check humidity anomaly
        IF ABS(humidity - hum_mean) > (std_dev_threshold * hum_std) THEN
            INSERT INTO detected_anomalies (sensor_data_id, metric_name, anomaly_value, expected_range_min, expected_range_max, severity)
            VALUES (sensor_id, 'humidity', humidity, 
                   hum_mean - (std_dev_threshold * hum_std), 
                   hum_mean + (std_dev_threshold * hum_std),
                   CASE 
                       WHEN ABS(humidity - hum_mean) > (3 * hum_std) THEN 'critical'
                       WHEN ABS(humidity - hum_mean) > (2.5 * hum_std) THEN 'high'
                       ELSE 'medium'
                   END);
        END IF;
        
        -- Check water level anomaly
        IF ABS(water_level - level_mean) > (std_dev_threshold * level_std) THEN
            INSERT INTO detected_anomalies (sensor_data_id, metric_name, anomaly_value, expected_range_min, expected_range_max, severity)
            VALUES (sensor_id, 'water_level', water_level, 
                   level_mean - (std_dev_threshold * level_std), 
                   level_mean + (std_dev_threshold * level_std),
                   CASE 
                       WHEN ABS(water_level - level_mean) > (3 * level_std) THEN 'critical'
                       WHEN ABS(water_level - level_mean) > (2.5 * level_std) THEN 'high'
                       ELSE 'medium'
                   END);
        END IF;
        
    END LOOP;
    
    CLOSE reading_cursor;
    
    -- Return current anomalies
    SELECT da.*, sd.timestamp as reading_time
    FROM detected_anomalies da
    JOIN sensor_data sd ON da.sensor_data_id = sd.id
    WHERE da.resolved = FALSE
    ORDER BY da.detected_at DESC;
    
END //

DELIMITER ;

-- Function to calculate data quality score
DELIMITER //

CREATE FUNCTION CalculateDataQuality(hours_back INT) 
RETURNS DECIMAL(5,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total_expected INT;
    DECLARE total_complete INT;
    DECLARE quality_score DECIMAL(5,2);
    
    -- Calculate expected readings (assuming hourly readings)
    SET total_expected = hours_back;
    
    -- Count complete readings (no NULL values in key metrics)
    SELECT COUNT(*) INTO total_complete
    FROM sensor_data 
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL hours_back HOUR)
    AND air_temperature IS NOT NULL 
    AND humidity IS NOT NULL 
    AND water_level IS NOT NULL
    AND water_temp_depth1 IS NOT NULL;
    
    -- Calculate quality percentage
    IF total_expected > 0 THEN
        SET quality_score = (total_complete / total_expected) * 100;
    ELSE 
        SET quality_score = 0;
    END IF;
    
    RETURN LEAST(quality_score, 100.00);
END //

DELIMITER ;

-- Sample queries for testing the new features:

-- Test the statistics procedure
-- CALL GetSensorStatistics(24);

-- Test anomaly detection
-- CALL DetectAnomalies(24, 2.0);

-- Test data quality function
-- SELECT CalculateDataQuality(24) as data_quality_percentage;

-- View latest analysis data
-- SELECT * FROM latest_sensor_analysis;