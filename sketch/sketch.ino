#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <DHT.h>

// Wi-Fi credentials
const char* ssid = "Keshaka";
const char* password = "Qwer3552";

// Server URL
const char* serverUrl = "http://blimas.pasgorasa.site/upload.php";

// WiFiClient object
WiFiClient wifiClient;

// Pin definitions
#define ONE_WIRE_BUS D2       // DS18B20 connected to pin D2
#define DHT_PIN D3            // DHT11 connected to pin D3
#define DHT_TYPE DHT11        // Define the type of DHT sensor
#define TRIG_PIN D4           // Ultrasonic sensor TRIG pin connected to D4
#define ECHO_PIN D5           // Ultrasonic sensor ECHO pin connected to D5

// Sensor objects
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature ds18b20(&oneWire);
DHT dht(DHT_PIN, DHT_TYPE);

void setup() {
  Serial.begin(115200);
  
  // Start sensors
  ds18b20.begin();
  dht.begin();
  
  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to Wi-Fi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nConnected to Wi-Fi.");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    // Read DS18B20 temperatures
    ds18b20.requestTemperatures();
    float temp1 = ds18b20.getTempCByIndex(0); // First DS18B20 sensor
    float temp2 = ds18b20.getTempCByIndex(1); // Second DS18B20 sensor
    float temp3 = ds18b20.getTempCByIndex(2); // Third DS18B20 sensor

    // Read DHT11 sensor
    float humidity = dht.readHumidity();
    float tempDHT = dht.readTemperature();

    // Read ultrasonic distance
    long duration;
    float distance;
    digitalWrite(TRIG_PIN, LOW);
    delayMicroseconds(2);
    digitalWrite(TRIG_PIN, HIGH);
    delayMicroseconds(10);
    digitalWrite(TRIG_PIN, LOW);
    duration = pulseIn(ECHO_PIN, HIGH);
    distance = duration * 0.034 / 2; // Convert to cm

    // Debugging output
    Serial.println("Sensor Readings:");
    Serial.printf("Temp1: %.2f °C, Temp2: %.2f °C, Temp3: %.2f °C\n", temp1, temp2, temp3);
    Serial.printf("Humidity: %.2f%%, TempDHT: %.2f °C\n", humidity, tempDHT);
    Serial.printf("Distance: %.2f cm\n", distance);

    // Prepare POST data
    String postData = "temp1=" + String(temp1) +
                      "&temp2=" + String(temp2) +
                      "&temp3=" + String(temp3) +
                      "&humidity=" + String(humidity) +
                      "&tempDHT=" + String(tempDHT) +
                      "&distance=" + String(distance);

    // Send data to the server
    http.begin(wifiClient, serverUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpCode = http.POST(postData);
    if (httpCode > 0) {
      String payload = http.getString();
      Serial.println("Server Response: " + payload);
    } else {
      Serial.println("Error sending POST: " + String(httpCode));
    }

    http.end();
  } else {
    Serial.println("Wi-Fi not connected!");
  }

  delay(5000); // Send data every 5 seconds
}