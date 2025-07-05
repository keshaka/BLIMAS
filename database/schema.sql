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

-- Insert sample data
INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3) VALUES
(28.5, 75.2, 2.45, 26.8, 25.9, 24.1),
(29.1, 73.8, 2.48, 27.2, 26.1, 24.3),
(27.9, 76.5, 2.42, 26.5, 25.7, 23.9),
(30.2, 71.3, 2.51, 27.8, 26.4, 24.5),
(28.8, 74.6, 2.46, 27.0, 25.8, 24.0);