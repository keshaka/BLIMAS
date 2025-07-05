#!/bin/bash
# BLIMAS Deployment Script for Ubuntu VPS

echo "Starting BLIMAS deployment..."

# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install apache2 mysql-server php php-mysql php-curl php-mbstring -y

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires

# Create database
sudo mysql -e "CREATE DATABASE IF NOT EXISTS blimas_db;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'blimas_user'@'localhost' IDENTIFIED BY 'secure_password_here';"
sudo mysql -e "GRANT ALL PRIVILEGES ON blimas_db.* TO 'blimas_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Import database schema
mysql -u blimas_user -p blimas_db < database/schema.sql

# Set up web directory
sudo mkdir -p /var/www/html/blimas
sudo cp -r * /var/www/html/blimas/
sudo chown -R www-data:www-data /var/www/html/blimas
sudo chmod -R 755 /var/www/html/blimas

# Create logs directory
sudo mkdir -p /var/www/html/blimas/logs
sudo chown www-data:www-data /var/www/html/blimas/logs

# Set up Apache virtual host
sudo tee /etc/apache2/sites-available/blimas.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName blimas.local
    DocumentRoot /var/www/html/blimas
    
    <Directory /var/www/html/blimas>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/blimas_error.log
    CustomLog \${APACHE_LOG_DIR}/blimas_access.log combined
</VirtualHost>
EOF

# Enable the site
sudo a2ensite blimas.conf
sudo a2dissite 000-default.conf

# Set up cron job for system monitoring
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/bin/php /var/www/html/blimas/scripts/system_monitor.php") | crontab -

# Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql

echo "BLIMAS deployment completed!"
echo "Please update the following configuration files:"
echo "1. config/database.php - Update database credentials"
echo "2. config/weather.php - Add your OpenWeatherMap API key"
echo "3. scripts/insert_sensor_data.php - Set your sensor API key"
echo ""
echo "To test the installation, run:"
echo "php scripts/data_simulator.php"
echo ""
echo "Access your BLIMAS dashboard at: http://your-server-ip/blimas"