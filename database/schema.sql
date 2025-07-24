-- Create the database
CREATE DATABASE IF NOT EXISTS blimas_db;

-- Use the database
USE blimas_db;

-- Create the sensor_data table
CREATE TABLE IF NOT EXISTS sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    air_temperature DECIMAL(5,2),
    humidity DECIMAL(5,2),
    water_level DECIMAL(8,2),
    water_temp_depth1 DECIMAL(5,2),
    water_temp_depth2 DECIMAL(5,2),
    water_temp_depth3 DECIMAL(5,2),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create the battery_status table
CREATE TABLE IF NOT EXISTS battery_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    battery_percentage INT,
    is_charging TINYINT(1),
    rssi INT
);