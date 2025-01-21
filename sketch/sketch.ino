#include <OneWire.h>
#include <DallasTemperature.h>
#include <DHT.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>


const char* ssid = "Keshaka";        
const char* password = "Qwer3552";
const char* server = "http://54.255.154.200/upload.php";

WiFiClient wifiClient;


// DS18B20 setup
#define ONE_WIRE_BUS D3
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature ds18b20(&oneWire);

// DHT22 setup
#define DHTPIN D4
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

// JSN-SR04 setup 
#define TRIG_PIN D5
#define ECHO_PIN D6

void setup() {
  Serial.begin(9600);
  WiFi.begin(ssid, password);
  Serial.print("Connecting to Wi-Fi");
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  
  // Initialize DS18B20
  ds18b20.begin();
  
  // Initialize DHT22
  dht.begin();
  
  // Initialize JSN-SR04 pins
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  
  Serial.println("Hello. Welcome to BLIMAS system");
  Serial.println("Connected to WiFi");
  Serial.println("Sensor Test Initialized!");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

  // Read DS18B20 sensors
  ds18b20.requestTemperatures();
  float temp1 = ds18b20.getTempCByIndex(0); // First DS18B20 sensor
  float temp2 = ds18b20.getTempCByIndex(1); // Second DS18B20 sensor
  float temp3 = ds18b20.getTempCByIndex(2); // Third DS18B20 sensor
  Serial.print("DS18B20 Sensor 1 = ");
  Serial.print(temp1);
  Serial.println(" °C");
  Serial.print("DS18B20 Sensor 2 = ");
  Serial.print(temp2);
  Serial.println(" °C");
  Serial.print("DS18B20 Sensor 3 = ");
  Serial.print(temp3);
  Serial.println(" °C");



  // Read DHT22 sensor
  float dhtTemp = dht.readTemperature();
  float humidity = dht.readHumidity();
  if (isnan(dhtTemp) || isnan(humidity)) {
    Serial.println("Failed to read from DHT22");
  } else {
    Serial.print("DHT22 Temperature: ");
    Serial.print(dhtTemp);
    Serial.println(" °C");
    Serial.print("DHT22 Humidity: ");
    Serial.print(humidity);
    Serial.println(" %");
  }

  // Read JSN-SR04 sensor
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH);
  float distance = 500 - (duration * 0.034 / 2); // Convert to cm

  Serial.print("JSN-SR04 Distance: ");
  Serial.print(distance);
  Serial.println(" cm");

  http.begin(wifiClient, server);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  // Format sensor data as POST parameters
    String postData = "temp1=" + String(temp1) +
                      "&temp2=" + String(temp2) +
                      "&temp3=" + String(temp3) +
                      "&humidity=" + String(humidity) +
                      "&tempDHT=" + String(dhtTemp) +
                      "&distance=" + String(distance);

    int httpResponseCode = http.POST(postData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Server Response: " + response);
    } else {
      Serial.println("Error in sending data");
    }

    http.end();

  }

  // Wait a bit before the next reading
  delay(5000);
}
