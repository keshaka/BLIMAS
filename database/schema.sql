-- BLIMAS Database Schema
CREATE DATABASE IF NOT EXISTS blimas_db;
USE blimas_db;

-- Sensor data table
CREATE TABLE sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    air_temperature DECIMAL(5,2),
    humidity DECIMAL(5,2),
    water_level DECIMAL(8,2),
    water_temp_depth1 DECIMAL(5,2),
    water_temp_depth2 DECIMAL(5,2),
    water_temp_depth3 DECIMAL(5,2),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp)
);

-- Battery status table
CREATE TABLE battery_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    battery_level DECIMAL(5,2),
    voltage DECIMAL(5,2),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp)
);

-- System settings table for optimal ranges
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_type VARCHAR(50) NOT NULL,
    min_optimal DECIMAL(8,2),
    max_optimal DECIMAL(8,2),
    min_warning DECIMAL(8,2),
    max_warning DECIMAL(8,2),
    min_critical DECIMAL(8,2),
    max_critical DECIMAL(8,2),
    unit VARCHAR(10),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_sensor_type (sensor_type)
);

-- Insert sample data
INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3) VALUES
(28.5, 75.2, 2.45, 26.8, 25.9, 24.1),
(29.1, 73.8, 2.48, 27.2, 26.1, 24.3),
(27.9, 76.5, 2.42, 26.5, 25.7, 23.9),
(30.2, 71.3, 2.51, 27.8, 26.4, 24.5),
(28.8, 74.6, 2.46, 27.0, 25.8, 24.0);

-- Insert sample battery data
INSERT INTO battery_status (battery_level, voltage) VALUES
(85.2, 12.4),
(84.8, 12.3),
(83.5, 12.2),
(82.1, 12.1),
(81.7, 12.0);

-- Insert system settings for optimal ranges
INSERT INTO system_settings (sensor_type, min_optimal, max_optimal, min_warning, max_warning, min_critical, max_critical, unit) VALUES
('air_temperature', 20.0, 35.0, 15.0, 40.0, 10.0, 45.0, '°C'),
('humidity', 40.0, 80.0, 30.0, 90.0, 20.0, 95.0, '%'),
('water_level', 1.5, 3.0, 1.0, 3.5, 0.5, 4.0, 'm'),
('water_temperature', 20.0, 30.0, 15.0, 35.0, 10.0, 40.0, '°C'),
('battery_level', 70.0, 100.0, 50.0, 100.0, 20.0, 100.0, '%'),
('voltage', 11.5, 13.0, 11.0, 13.5, 10.5, 14.0, 'V');