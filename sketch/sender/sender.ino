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
#define ONE_WIRE_BUS 40  // DS18B20 data pin
#define DHTPIN 19       // DHT22 data pin
#define DHTTYPE DHT22   // DHT sensor type
#define TRIG_PIN 46     // JSN-SR04T trigger pin
#define ECHO_PIN 45     // JSN-SR04T echo pin

// LoRa Configurations
#define RF_FREQUENCY 433300000
#define TX_OUTPUT_POWER 14

#define sleepTime 1

// Initialize Sensors   
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature ds18b20(&oneWire);
DHT dht(DHTPIN, DHTTYPE);
SSD1306Wire mdisplay(0x3c, 500000, SDA_OLED, SCL_OLED, GEOMETRY_128_64, RST_OLED);

// LoRa Variables
char txpacket[100];
char rxpacket[100];
static RadioEvents_t RadioEvents;

// Function Prototypes
void readSensors();
void sendLoRaData();
void displayData();

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
    Radio.SetTxConfig(MODEM_LORA, TX_OUTPUT_POWER, 0, 0, 7, 1, 8, false, true, 0, 0, false, 3000);
}

void loop() {
    readSensors();
    //displayData();
    sendLoRaData();
    delay(5000);

    // Configure deep sleep
    Serial.println("Going to deep sleep...");
    esp_sleep_enable_timer_wakeup(sleepTime * 60 * 60 * 1000000);
    esp_deep_sleep_start();
}

void readSensors() {
    ds18b20.requestTemperatures();
    float temp1 = ds18b20.getTempCByIndex(0);
    float temp2 = ds18b20.getTempCByIndex(1);
    float temp3 = ds18b20.getTempCByIndex(2);
    float airTemp = dht.readTemperature();
    float humidity = dht.readHumidity();

    // Water level measurement
    digitalWrite(TRIG_PIN, LOW);
    delayMicroseconds(2);
    digitalWrite(TRIG_PIN, HIGH);
    delayMicroseconds(10);
    digitalWrite(TRIG_PIN, LOW);
    long duration = pulseIn(ECHO_PIN, HIGH);
    float waterLevel = duration * 0.034 / 2;

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
    if (waterLevel>0) {
        waterLevel = waterLevel;
    }
    else {
        waterLevel = 0;
    }

    snprintf(txpacket, sizeof(txpacket), "LM|3552|T1:%.2f,T2:%.2f,T3:%.2f,AirT:%.2f,H:%.2f,W:%.2f",
             temp1, temp2, temp3, airTemp, humidity, waterLevel);
}

void displayData() {
    mdisplay.clear();
    mdisplay.drawString(0, 0, "BLIMAS SYSTEM");
    String receivedData = txpacket;
    String line1 = receivedData.substring(0, 22);  // First 18 characters
    String line2 = receivedData.substring(22, 48); // Next 18 characters
    String line3 = receivedData.substring(48, 71); // Next 18 characters
    //String line4 = receivedData.substring(54, 72);
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
}
