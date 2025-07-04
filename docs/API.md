# BLIMAS API Documentation

## Overview

The BLIMAS API provides real-time access to sensor data, weather information, and system status. All endpoints return JSON data and support cross-origin requests.

## Base URL

```
https://your-domain.com/api/
```

## Authentication

Currently, the API does not require authentication. For production environments, consider implementing API key authentication.

## Rate Limiting

No rate limiting is currently implemented. Consider implementing rate limiting for production use.

## Endpoints

### 1. Get Latest Sensor Data

Retrieves the most recent sensor readings from all monitoring points.

**Endpoint:** `GET /api/data.php?action=latest`

**Response:**
```json
{
  "success": true,
  "data": {
    "air_temperature": 28.5,
    "humidity": 75.2,
    "water_level": 120.5,
    "water_temp1": 26.8,
    "water_temp2": 25.9,
    "water_temp3": 24.7,
    "battery_level": 85.3,
    "timestamp": "2024-01-15 14:30:25",
    "last_updated": "2024-01-15 14:30:25"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "No sensor data found"
}
```

### 2. Get Weather Data

Retrieves current weather information for Katubedda, Sri Lanka.

**Endpoint:** `GET /api/data.php?action=weather`

**Response:**
```json
{
  "success": true,
  "data": {
    "location": "Katubedda, LK",
    "temperature": 29.2,
    "humidity": 78,
    "wind_speed": 12.5,
    "wind_direction": "SW",
    "precipitation": 0,
    "weather_condition": "Clear",
    "weather_description": "clear sky",
    "icon": "01d",
    "pressure": 1013,
    "visibility": 10,
    "timestamp": "2024-01-15 14:30:25"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Failed to fetch weather data"
}
```

### 3. Get Historical Data

Retrieves historical data for a specific parameter.

**Endpoint:** `GET /api/data.php?action=historical`

**Parameters:**
- `parameter` (required): The sensor parameter to retrieve
  - Valid values: `air_temperature`, `humidity`, `water_level`, `water_temp1`, `water_temp2`, `water_temp3`
- `limit` (optional): Number of records to return (default: 50, max: 200)

**Example:** `GET /api/data.php?action=historical&parameter=air_temperature&limit=20`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "value": 28.5,
      "timestamp": "2024-01-15 14:25:25"
    },
    {
      "value": 28.7,
      "timestamp": "2024-01-15 14:30:25"
    }
  ],
  "parameter": "air_temperature"
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Invalid parameter"
}
```

### 4. Get Water Temperature Data

Retrieves historical data for all water temperature sensors.

**Endpoint:** `GET /api/data.php?action=water_temperature`

**Parameters:**
- `limit` (optional): Number of records to return (default: 50, max: 200)

**Example:** `GET /api/data.php?action=water_temperature&limit=30`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "temp1": 26.8,
      "temp2": 25.9,
      "temp3": 24.7,
      "timestamp": "2024-01-15 14:25:25"
    },
    {
      "temp1": 26.9,
      "temp2": 26.0,
      "temp3": 24.8,
      "timestamp": "2024-01-15 14:30:25"
    }
  ]
}
```

### 5. Get System Status

Retrieves system status and health information.

**Endpoint:** `GET /api/data.php?action=status`

**Response:**
```json
{
  "success": true,
  "data": {
    "last_update": "2024-01-15 14:30:25",
    "seconds_since_update": 45,
    "total_records": 1250,
    "status": "online",
    "database_connected": true
  }
}
```

**Status Values:**
- `online`: System is receiving data within the last 10 minutes
- `offline`: No data received for more than 10 minutes
- `error`: System error occurred

### 6. Legacy Sensor Data Endpoint

For backward compatibility with existing systems.

**Endpoint:** `GET /get_sensor_data.php`

**Response:**
```json
{
  "success": true,
  "data": {
    "air_temperature": 28.5,
    "humidity": 75.2,
    "water_level": 120.5,
    "water_temp1": 26.8,
    "water_temp2": 25.9,
    "water_temp3": 24.7,
    "battery_level": 85.3,
    "timestamp": "2024-01-15 14:30:25",
    "id": 1250
  }
}
```

## Data Submission

### Upload Sensor Data

Submit new sensor readings to the system.

**Endpoint:** `POST /upload.php`

**Parameters:**
- `water_temp1` (required): Water temperature at depth 1 in Celsius
- `water_temp2` (required): Water temperature at depth 2 in Celsius
- `water_temp3` (required): Water temperature at depth 3 in Celsius
- `humidity` (required): Humidity percentage (0-100)
- `air_temp` (required): Air temperature in Celsius
- `water_level` (required): Water level in centimeters
- `battery_level` (optional): Battery level percentage (0-100)

**Example Request:**
```bash
curl -X POST https://your-domain.com/upload.php \
  -d "water_temp1=26.5" \
  -d "water_temp2=25.8" \
  -d "water_temp3=24.9" \
  -d "humidity=75.2" \
  -d "air_temp=28.5" \
  -d "water_level=120.5" \
  -d "battery_level=85.3"
```

**Success Response:**
```
Data inserted successfully.
```

**Error Response:**
```
Missing POST data.
```

## Error Handling

All API endpoints return consistent error responses:

```json
{
  "success": false,
  "error": "Error description"
}
```

Common error codes:
- **400 Bad Request**: Invalid parameters
- **404 Not Found**: Endpoint not found
- **500 Internal Server Error**: Database or system error

## Data Types

### Sensor Data Types
- **Temperature values**: Float with 2 decimal places (°C)
- **Humidity**: Float with 2 decimal places (%)
- **Water level**: Float with 2 decimal places (cm)
- **Battery level**: Float with 2 decimal places (%)
- **Timestamps**: MySQL DATETIME format (YYYY-MM-DD HH:MM:SS)

### Weather Data Types
- **Temperature**: Float (°C)
- **Humidity**: Integer (%)
- **Wind speed**: Float (km/h)
- **Precipitation**: Float (mm)
- **Pressure**: Integer (hPa)
- **Visibility**: Float (km)

## Caching

### Weather Data Caching
- Weather data is cached for 10 minutes
- Cache is automatically cleaned of expired entries
- Manual cache cleaning: `DELETE FROM weather_cache WHERE expires_at < NOW()`

### Database Performance
- Sensor data queries are limited to recent records
- Indexes are available on timestamp columns
- Historical data older than specified limits may require direct database queries

## Usage Examples

### JavaScript/AJAX

```javascript
// Get latest sensor data
fetch('/api/data.php?action=latest')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Temperature:', data.data.air_temperature);
      console.log('Humidity:', data.data.humidity);
    }
  });

// Get historical temperature data
fetch('/api/data.php?action=historical&parameter=air_temperature&limit=20')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      data.data.forEach(point => {
        console.log(`${point.timestamp}: ${point.value}°C`);
      });
    }
  });
```

### PHP

```php
// Get latest sensor data
$response = file_get_contents('https://your-domain.com/api/data.php?action=latest');
$data = json_decode($response, true);

if ($data['success']) {
    echo "Temperature: " . $data['data']['air_temperature'] . "°C\n";
    echo "Humidity: " . $data['data']['humidity'] . "%\n";
}
```

### Python

```python
import requests

# Get latest sensor data
response = requests.get('https://your-domain.com/api/data.php?action=latest')
data = response.json()

if data['success']:
    print(f"Temperature: {data['data']['air_temperature']}°C")
    print(f"Humidity: {data['data']['humidity']}%")

# Submit sensor data
data = {
    'water_temp1': 26.5,
    'water_temp2': 25.8,
    'water_temp3': 24.9,
    'humidity': 75.2,
    'air_temp': 28.5,
    'water_level': 120.5,
    'battery_level': 85.3
}

response = requests.post('https://your-domain.com/upload.php', data=data)
print(response.text)
```

## Webhook Support

Currently, webhooks are not implemented. Consider adding webhook support for real-time notifications when:
- Sensor values exceed thresholds
- System goes offline
- Battery levels are low

## Future Enhancements

Planned API improvements:
1. **Authentication**: API key-based authentication
2. **Rate Limiting**: Configurable rate limits per client
3. **Data Aggregation**: Hourly, daily, monthly averages
4. **Alerts**: Threshold-based alerting system
5. **Bulk Operations**: Batch data submission
6. **GraphQL Support**: More flexible query capabilities
7. **WebSocket Support**: Real-time data streaming

## Support

For API-related issues:
1. Check this documentation
2. Verify endpoint URLs and parameters
3. Check network connectivity
4. Review server logs
5. Open an issue on the GitHub repository