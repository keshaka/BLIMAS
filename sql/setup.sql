-- BLIMAS Database Setup Script
-- Run this script to set up the database tables

-- Create sensor_data table (if not exists)
CREATE TABLE IF NOT EXISTS sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    air_temperature FLOAT COMMENT 'Air temperature from DHT sensor',
    humidity FLOAT COMMENT 'Humidity percentage from DHT sensor', 
    water_level FLOAT COMMENT 'Water level in cm',
    water_temp_depth1 FLOAT COMMENT 'Water temperature at depth 1',
    water_temp_depth2 FLOAT COMMENT 'Water temperature at depth 2', 
    water_temp_depth3 FLOAT COMMENT 'Water temperature at depth 3',
    battery_level FLOAT COMMENT 'Battery level percentage (admin only)',
    INDEX idx_timestamp (timestamp)
);

-- Update existing table to match new column names if needed
-- (The existing upload.php uses water_temp1, water_temp2, water_temp3, air_temp)
-- We'll keep both formats for compatibility

-- Add new columns if they don't exist (MySQL will ignore if they exist)
ALTER TABLE sensor_data 
ADD COLUMN IF NOT EXISTS air_temperature FLOAT COMMENT 'Air temperature from DHT sensor',
ADD COLUMN IF NOT EXISTS water_temp_depth1 FLOAT COMMENT 'Water temperature at depth 1',
ADD COLUMN IF NOT EXISTS water_temp_depth2 FLOAT COMMENT 'Water temperature at depth 2',
ADD COLUMN IF NOT EXISTS water_temp_depth3 FLOAT COMMENT 'Water temperature at depth 3';

-- Create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_remember_token (remember_token)
);

-- Insert default admin user (password: admin123)
-- Change this password immediately after setup!
INSERT IGNORE INTO admin_users (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Create weather_cache table for caching weather API responses
CREATE TABLE IF NOT EXISTS weather_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(100) NOT NULL,
    weather_data JSON,
    cached_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_location_time (location, cached_at)
);

-- Create system_logs table for error and activity logging
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_level ENUM('INFO', 'WARNING', 'ERROR', 'DEBUG') NOT NULL,
    message TEXT NOT NULL,
    context JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level_time (log_level, created_at)
);

-- Sample data for testing (remove in production)
-- INSERT INTO sensor_data (air_temperature, humidity, water_level, water_temp_depth1, water_temp_depth2, water_temp_depth3, battery_level)
-- VALUES 
-- (26.5, 75.2, 150.3, 24.1, 23.8, 23.5, 85.6),
-- (27.1, 73.8, 149.8, 24.3, 24.0, 23.7, 84.2),
-- (25.9, 76.5, 151.2, 23.9, 23.6, 23.3, 83.8);