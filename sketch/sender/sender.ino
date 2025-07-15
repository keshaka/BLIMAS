//blimas sender

#include "esp_sleep.h"
#include <Arduino.h>
#include <Wire.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <DHT.h>
#include "LoRaWan_APP.h"
#include "HT_SSD1306Wire.h"

// Pin Definitions
#define ONE_WIRE_BUS 19  // DS18B20 data pin
#define DHTPIN 4       // DHT22 data pin
#define DHTTYPE DHT22   // DHT sensor type
#define TRIG_PIN 46     // JSN-SR04T trigger pin
#define ECHO_PIN 45     // JSN-SR04T echo pin
#define VBAT_Read    1
#define	ADC_Ctrl    37
#define LED_PIN 40

// LoRa Configurations
#define RF_FREQUENCY 433300000
#define TX_OUTPUT_POWER 20
#define LORA_SPREADING_FACTOR 12

#define sleepTime 5  // minutes

// Initialize Sensors   
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature ds18b20(&oneWire);
DHT dht(DHTPIN, DHTTYPE);
SSD1306Wire mdisplay(0x3c, 500000, SDA_OLED, SCL_OLED, GEOMETRY_128_64, RST_OLED);

// LoRa Variables
char txpacket[100];
char rxpacket[100];
static RadioEvents_t RadioEvents;

int voltageToPercent(float voltage) {
  if (voltage >= 4.2) return 100;
  else if (voltage >= 4.0) return 85 + (voltage - 4.0) * 75;
  else if (voltage >= 3.85) return 60 + (voltage - 3.85) * 166;
  else if (voltage >= 3.7) return 40 + (voltage - 3.7) * 133;
  else if (voltage >= 3.5) return 15 + (voltage - 3.5) * 125;
  else if (voltage >= 3.3) return 5 + (voltage - 3.3) * 50;
  else return 0;
}

int readBatteryVoltage() {
  // ADC resolution
  const int resolution = 12;
  const int adcMax = pow(2,resolution) - 1;
  const float adcMaxVoltage = 3.3;
  // On-board voltage divider
  const int R1 = 390;
  const int R2 = 100;
  // Calibration measurements
  const float measuredVoltage = 4.2;
  const float reportedVoltage = 4.095;
  // Calibration factor
  const float factor = (adcMaxVoltage / adcMax) * ((R1 + R2)/(float)R2) * (measuredVoltage / reportedVoltage); 
  digitalWrite(ADC_Ctrl,LOW);
  delay(100);
  int analogValue = analogRead(VBAT_Read);
  digitalWrite(ADC_Ctrl,HIGH);

  float floatVoltage = factor * analogValue;
  uint16_t voltage = (int)(floatVoltage * 1000.0);
  int batteryPercent = voltageToPercent(floatVoltage);
  return batteryPercent;
  //    delay(10000);
}

float measureWaterLevel() {
  float waterLevel = 0;
  int maxTries = 5;
  int attempt = 0;

  while (attempt < maxTries) {
    // Trigger the sensor
    digitalWrite(TRIG_PIN, LOW);
    delayMicroseconds(2);
    digitalWrite(TRIG_PIN, HIGH);
    delayMicroseconds(10);
    digitalWrite(TRIG_PIN, LOW);

    // Read the echo
    long duration = pulseIn(ECHO_PIN, HIGH, 30000); // 30ms timeout for safety
    waterLevel = duration * 0.034 / 2;

    if (waterLevel > 0) {
      break;  // Valid measurement, exit loop
    }

    attempt++;
    delay(50); // Small delay between retries
  }

  return waterLevel;
}

void readSensors() {
    ds18b20.requestTemperatures();
    float temp1 = ds18b20.getTempCByIndex(0);
    float temp2 = ds18b20.getTempCByIndex(1);
    float temp3 = ds18b20.getTempCByIndex(2);
    float airTemp = dht.readTemperature();
    float humidity = dht.readHumidity();

    // Water level measurement
    float waterLevel = 207 - measureWaterLevel();

    int btrl = readBatteryVoltage();

    if (temp1==-127.00) {
          temp1 = 0;
        }
    if (temp2==-127.00) {
          temp2 = 0;
        }
    if (temp3==-127.00) {
          temp3 = 0;
        }
    if (airTemp>0) {
        airTemp = airTemp;
    }
    else {
        airTemp = 0;
    }
    if (humidity>0) {
        humidity = humidity;
    }
    else {
        humidity = 0;
    }


    snprintf(txpacket, sizeof(txpacket), "LM|3552|T1:%.2f,T2:%.2f,T3:%.2f,AirT:%.2f,H:%.2f,W:%.2f,B:%d",
             temp1, temp2, temp3, airTemp, humidity, waterLevel, btrl);
}

void displayData() {
    mdisplay.clear();
    mdisplay.drawString(0, 0, "BLIMAS SYSTEM");
    String receivedData = txpacket;
    String line1 = receivedData.substring(0, 22);  // First 18 characters
    String line2 = receivedData.substring(22, 48); // Next 18 characters
    String line3 = receivedData.substring(48, 71); // Next 18 characters
    //String line4 = receivedData.substring(71, 80);
    //String line5 = receivedData.substring(72, 80);
    //mdisplay.drawString(0, 10, String(txpacket));
    mdisplay.drawString(0, 10, line1);
    mdisplay.drawString(0, 20, line2);
    mdisplay.drawString(0, 30, line3);
    //mdisplay.drawString(0, 40, line4);
    //mdisplay.drawString(0, 50, line5);
    mdisplay.display();
}

void sendLoRaData() {
    Serial.printf("Sending LoRa: %s\n", txpacket);
    Radio.Send((uint8_t *)txpacket, strlen(txpacket));
    digitalWrite(LED_PIN, HIGH);
    delay(500);
}

void setup() {
    Serial.begin(115200);
    ds18b20.begin();
    dht.begin();
    pinMode(TRIG_PIN, OUTPUT);
    pinMode(ECHO_PIN, INPUT);

    mdisplay.init();
    mdisplay.clear();
    mdisplay.display();
    mdisplay.drawString(0, 0, "BLIMAS data collector");
    mdisplay.drawString(0, 10, "Initializing...");
    mdisplay.display();

    // LoRa Setup
    Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);
    RadioEvents.TxDone = [](){ Serial.println("LoRa TX Done"); };
    Radio.Init(&RadioEvents);
    Radio.SetChannel(RF_FREQUENCY);
    Radio.SetTxConfig(MODEM_LORA, TX_OUTPUT_POWER, 0, 0, LORA_SPREADING_FACTOR, 1, 8, false, true, 0, 0, false, 3000);

    pinMode(ADC_Ctrl,OUTPUT);
    pinMode(VBAT_Read,INPUT);
    //adcAttachPin(VBAT_Read);
    analogReadResolution(12);
    pinMode(LED_PIN, OUTPUT);
}

void loop() {
    delay(1000);
    readSensors();
    displayData();
    sendLoRaData();
    delay(5000);

    // Configure deep sleep
    Serial.println("Going to deep sleep...");
    esp_sleep_enable_timer_wakeup(sleepTime * 60 * 1000000);
    esp_deep_sleep_start();
}

