-- BLIMAS Database Schema
-- Bolgoda Lake Information Monitoring & Analysis System

CREATE DATABASE IF NOT EXISTS blimas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blimas_db;

-- Create main sensor data table
CREATE TABLE IF NOT EXISTS sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    air_temperature DECIMAL(5,2) NOT NULL COMMENT 'Air temperature in Celsius',
    humidity DECIMAL(5,2) NOT NULL COMMENT 'Humidity percentage',
    water_level DECIMAL(8,2) NOT NULL COMMENT 'Water level in cm',
    water_temp1 DECIMAL(5,2) NOT NULL COMMENT 'Water temperature at depth 1 in Celsius',
    water_temp2 DECIMAL(5,2) NOT NULL COMMENT 'Water temperature at depth 2 in Celsius', 
    water_temp3 DECIMAL(5,2) NOT NULL COMMENT 'Water temperature at depth 3 in Celsius',
    battery_level DECIMAL(5,2) DEFAULT NULL COMMENT 'Battery level percentage',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data collection timestamp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB COMMENT='Main sensor data storage table';

-- Create weather data cache table for external API data
CREATE TABLE IF NOT EXISTS weather_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(100) NOT NULL DEFAULT 'Katubedda',
    temperature DECIMAL(5,2) NOT NULL,
    humidity DECIMAL(5,2) NOT NULL,
    wind_speed DECIMAL(5,2) NOT NULL,
    wind_direction VARCHAR(10),
    precipitation DECIMAL(5,2) DEFAULT 0,
    weather_condition VARCHAR(100),
    weather_description TEXT,
    api_response JSON COMMENT 'Full API response for debugging',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL COMMENT 'When this cache entry expires',
    INDEX idx_location (location),
    INDEX idx_timestamp (timestamp),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB COMMENT='Weather API data cache';

-- Create system configuration table
CREATE TABLE IF NOT EXISTS system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB COMMENT='System configuration storage';

-- Insert default configuration values
INSERT INTO system_config (config_key, config_value, description) VALUES
('weather_api_key', '', 'OpenWeatherMap API key'),
('weather_location', 'Katubedda,LK', 'Default weather location'),
('data_refresh_interval', '5000', 'Data refresh interval in milliseconds'),
('chart_data_points', '50', 'Number of data points to show in charts'),
('system_name', 'BLIMAS', 'System name'),
('system_description', 'Bolgoda Lake Information Monitoring & Analysis System', 'System description'),
('timezone', 'Asia/Colombo', 'System timezone')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Create sample data for testing
INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp1, water_temp2, water_temp3, battery_level) VALUES
(28.5, 75.2, 120.5, 26.8, 25.9, 24.7, 85.3),
(29.1, 73.8, 118.9, 27.1, 26.2, 25.0, 84.7),
(27.9, 76.5, 122.1, 26.5, 25.7, 24.5, 86.1),
(30.2, 72.1, 117.3, 27.8, 26.9, 25.8, 83.9),
(28.8, 74.6, 119.7, 27.0, 26.1, 24.9, 85.5);