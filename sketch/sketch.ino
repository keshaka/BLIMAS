#include <ESP8266WiFi.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <DHT.h>
#include <NewPing.h>

// WiFi Credentials
const char* ssid = "Keshaka";
const char* password = "Qwer-3552";

// Server details
const char* server = "blimas.pasgorasa.site";
const int port = 80;

// DS18B10
#define ONE_WIRE_BUS D4
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

// DHT11
#define DHTPIN D5
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// JSN-SR04
#define TRIG_PIN D6
#define ECHO_PIN D7
NewPing sonar(TRIG_PIN, ECHO_PIN);

void setup() {
  Serial.begin(115200);

  // WiFi setup
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");

  // Sensor initialization
  sensors.begin();
  dht.begin();
}

void loop() {
  // Read sensors
  sensors.requestTemperatures();
  float temp1 = sensors.getTempCByIndex(0);
  float temp2 = sensors.getTempCByIndex(1);
  float temp3 = sensors.getTempCByIndex(2);
  float humidity = dht.readHumidity();
  float tempDHT = dht.readTemperature();
  float distance = sonar.ping_cm();

  // Prepare data
  String data = "temp1=" + String(temp1) + "&temp2=" + String(temp2) + 
                "&temp3=" + String(temp3) + "&humidity=" + String(humidity) + 
                "&tempDHT=" + String(tempDHT) + "&distance=" + String(distance);

  // Send data to server
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    if (client.connect(server, port)) {
      client.println("POST /upload HTTP/1.1");
      client.println("Host: blimas.pasgorasa.site");
      client.println("Content-Type: application/x-www-form-urlencoded");
      client.print("Content-Length: ");
      client.println(data.length());
      client.println();
      client.print(data);
      client.stop();
    }
  }

  delay(5000); // Send data every 5 seconds
}
