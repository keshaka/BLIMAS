#!/usr/bin/env python3
"""
BLIMAS Sensor Client
Send sensor data from Arduino/Raspberry Pi to BLIMAS web server
"""

import requests
import json
import time
import random
import logging
from datetime import datetime

# Configuration
BLIMAS_URL = "http://your-server.com/blimas/scripts/insert_sensor_data.php"
API_KEY = "your_sensor_api_key_here"
UPDATE_INTERVAL = 300  # 5 minutes

# Set up logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('sensor_client.log'),
        logging.StreamHandler()
    ]
)

def read_sensors():
    """
    Replace this function with actual sensor reading code
    This is just a simulation
    """
    return {
        'air_temp': round(25 + random.uniform(-5, 5), 1),
        'humidity': round(70 + random.uniform(-10, 10), 1),
        'water_level': round(2.5 + random.uniform(-0.2, 0.2), 2),
        'water_temp_1': round(24 + random.uniform(-2, 2), 1),
        'water_temp_2': round(23 + random.uniform(-2, 2), 1),
        'water_temp_3': round(22 + random.uniform(-2, 2), 1)
    }

def send_data(sensor_data):
    """Send sensor data to BLIMAS server"""
    try:
        data = sensor_data.copy()
        data['api_key'] = API_KEY
        
        response = requests.post(BLIMAS_URL, data=data, timeout=30)
        response.raise_for_status()
        
        result = response.json()
        if result['status'] == 'success':
            logging.info(f"Data sent successfully: {sensor_data}")
            return True
        else:
            logging.error(f"Server error: {result['message']}")
            return False
            
    except requests.exceptions.RequestException as e:
        logging.error(f"Network error: {e}")
        return False
    except json.JSONDecodeError as e:
        logging.error(f"Invalid response format: {e}")
        return False
    except Exception as e:
        logging.error(f"Unexpected error: {e}")
        return False

def main():
    logging.info("BLIMAS Sensor Client started")
    
    while True:
        try:
            # Read sensor data
            sensor_data = read_sensors()
            logging.info(f"Sensor readings: {sensor_data}")
            
            # Send to server
            if send_data(sensor_data):
                logging.info("Data transmission successful")
            else:
                logging.warning("Data transmission failed")
            
            # Wait for next update
            time.sleep(UPDATE_INTERVAL)
            
        except KeyboardInterrupt:
            logging.info("Sensor client stopped by user")
            break
        except Exception as e:
            logging.error(f"Unexpected error in main loop: {e}")
            time.sleep(60)  # Wait 1 minute before retrying

if __name__ == "__main__":
    main()