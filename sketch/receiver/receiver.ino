#include "LoRaWan_APP.h"
#include "Arduino.h"
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include <ESPmDNS.h>
#include <Update.h>
#include "HT_SSD1306Wire.h"
#include "esp_sleep.h"
#include <Preferences.h>

#define VBAT_Read    1
#define	ADC_Ctrl    37

// ======================= Persistent Storage =======================
Preferences preferences;

// ======================= Config Variables =========================

// Access Point config
char ap_ssid[32] = "BLIMAS";
char ap_password[64] = "";

// University WiFi config
char uni_ssid[32] = "UoM_Wireless";
char uni_password[64] = "";

// Captive portal login
char login_url[128] = "https://wlan.uom.lk/login.html";
char username[64] = "";
char user_password[64] = "";

// Remote server
char serverURL[128] = "http://blimas.unilodge.live/upload.php";

// LoRa Parameters
#define RF_FREQUENCY_DEFAULT 433300000
#define TX_OUTPUT_POWER 14
int LORA_SPREADING_FACTOR = 12;
int LORA_BANDWIDTH = 0;
int LORA_CODINGRATE = 1;
long RF_FREQUENCY = RF_FREQUENCY_DEFAULT;

// Other Globals
char rxpacket[128];
bool lora_idle = true;
int16_t rssi;
float temp1, temp2, temp3, humidity, tempDHT, distance;
int bat;
static RadioEvents_t RadioEvents;
bool apMode = true;

#define sleepTime 3  // minutes

// ======================= Hardware Objects =========================
WiFiClientSecure client;
WebServer server(80);
SSD1306Wire mdisplay(0x3c, 500000, SDA_OLED, SCL_OLED, GEOMETRY_128_64, RST_OLED);

// ======================= UI HTML Style ===========================
const char* style = "<style>body{font-family:Arial;background:#f4f4f4;margin:0;padding:20px;}h1{color:#333;}form{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}input[type=text],input[type=password]{width:100%;padding:10px;margin:8px 0;border:1px solid #ccc;border-radius:4px;}input[type=submit]{background-color:#4CAF50;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;}input[type=submit]:hover{background-color:#45a049}</style>";

// ======================= Function Prototypes =====================
void handleConfigPage();
void handleSaveConfig();
void setupHotspot();
void stopHotspot();
void connectToUniversityWiFi();
bool isCaptivePortal();
void disconnectWiFi();
void loginToCaptivePortal(const char* username, const char* user_password, const char* login_url);
void sendDataToServer(float temp1, float temp2, float temp3, float humidity, float tempDHT, float distance, int bat, int rssi, int btrl);
void checkConnection();
void setupWebServer();
void handleRoot();
void handleSetFreq();
void OnRxDone(uint8_t* payload, uint16_t size, int16_t _rssi, int8_t snr);

// ======================= Web Server Handlers =====================

void handleConfigPage() {
  String html = "<html><head><title>Configuration</title>";
  html += style;
  html += "</head><body><h1>BLIMAS Configuration</h1>";
  html += "<form method='POST' action='/saveconfig'>";
  html += "<label>Hotspot Password:</label><input type='text' name='ap_password' value='" + String(ap_password) + "'>";
  html += "<label>Portal Username:</label><input type='text' name='username' value='" + String(username) + "'>";
  html += "<label>Portal Password:</label><input type='password' name='user_password' value='" + String(user_password) + "'>";
  html += "<label>LoRa Frequency (Hz):</label><input type='text' name='freq' value='" + String(RF_FREQUENCY) + "'>";
  html += "<label>Bandwidth:</label><input type='text' name='bandwidth' value='" + String(LORA_BANDWIDTH) + "'>";
  html += "<label>Spreading Factor:</label><input type='text' name='sf' value='" + String(LORA_SPREADING_FACTOR) + "'>";
  html += "<label>Server URL:</label><input type='text' name='serverURL' value='" + String(serverURL) + "'>";
  html += "<input type='submit' value='Save Settings'></form></body></html>";
  server.send(200, "text/html", html);
}

void handleSaveConfig() {
  preferences.begin("blimas", false); // Open NVS storage

  if (server.hasArg("ap_password")) {
    strncpy(ap_password, server.arg("ap_password").c_str(), sizeof(ap_password) - 1);
    ap_password[sizeof(ap_password) - 1] = '\0';
    preferences.putString("ap_password", ap_password);
  }
  if (server.hasArg("username")) {
    strncpy(username, server.arg("username").c_str(), sizeof(username) - 1);
    username[sizeof(username) - 1] = '\0';
    preferences.putString("username", username);
  }
  if (server.hasArg("user_password")) {
    strncpy(user_password, server.arg("user_password").c_str(), sizeof(user_password) - 1);
    user_password[sizeof(user_password) - 1] = '\0';
    preferences.putString("user_password", user_password);
  }
  if (server.hasArg("freq")) {
    RF_FREQUENCY = atol(server.arg("freq").c_str());
    preferences.putLong("freq", RF_FREQUENCY);
  }
  if (server.hasArg("bandwidth")) {
    LORA_BANDWIDTH = atoi(server.arg("bandwidth").c_str());
    preferences.putInt("bandwidth", LORA_BANDWIDTH);
  }
  if (server.hasArg("sf")) {
    LORA_SPREADING_FACTOR = atoi(server.arg("sf").c_str());
    preferences.putInt("sf", LORA_SPREADING_FACTOR);
  }
  if (server.hasArg("serverURL")) {
    strncpy(serverURL, server.arg("serverURL").c_str(), sizeof(serverURL) - 1);
    serverURL[sizeof(serverURL) - 1] = '\0';
    preferences.putString("serverURL", serverURL);
  }

  preferences.end();

  Radio.SetChannel(RF_FREQUENCY);
  Radio.SetRxConfig(MODEM_LORA, LORA_BANDWIDTH, LORA_SPREADING_FACTOR, LORA_CODINGRATE, 0, 8, 0, false, 0, true, 0, 0, false, true);
  server.send(200, "text/html", "<html><body><h1>Settings Updated</h1><a href='/config'>Go Back</a></body></html>");
}

void handleRoot() {
  String html = "<html><head><title>BLIMAS Data</title>";
  html += style;
  html += "</head><body><h1>Last Received Packet</h1><pre>" + String(rxpacket) + "</pre>";
  html += "<a href='/config'>Edit Configuration</a><br><br>";
  html += "</body></html>";
  server.send(200, "text/html", html);
}

void handleSetFreq() {
  if (server.hasArg("freq")) {
    RF_FREQUENCY = atol(server.arg("freq").c_str());
    Radio.SetChannel(RF_FREQUENCY);
    server.send(200, "text/html", "<html><body><h1>Frequency Updated</h1><p>New frequency: " + String(RF_FREQUENCY) + " Hz</p><a href='/'>Go Back</a></body></html>");
    Serial.println("Frequency updated to: " + String(RF_FREQUENCY));
  } else {
    server.send(400, "text/html", "<html><body><h1>Missing Parameter</h1></body></html>");
  }
}

void setupWebServer() {
  server.on("/", handleRoot);
  server.on("/config", handleConfigPage);
  server.on("/saveconfig", HTTP_POST, handleSaveConfig);
  server.begin();
  Serial.println("Web server started on hotspot");
}

// ======================= Hotspot & WiFi =========================

void setupHotspot() {
  WiFi.softAP(ap_ssid, ap_password);
  Serial.println("Hotspot started: blimas");
  IPAddress IP = WiFi.softAPIP();
  Serial.print("AP IP address: ");
  Serial.println(IP);
  mdisplay.clear();
  mdisplay.drawString(0, 0, "Hotspot started.");
  mdisplay.drawString(0, 10, "SSID - BLIMAS");
  mdisplay.display();
}

void stopHotspot() {
  WiFi.softAPdisconnect(true);
  Serial.println("Hotspot stopped");
}

void connectToUniversityWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(uni_ssid, uni_password);
  Serial.print("Connecting to university WiFi...");
  mdisplay.clear();
  mdisplay.drawString(0, 0, "Connecting to university WiFi...");
  mdisplay.display();
  int tries = 0;
  while (WiFi.status() != WL_CONNECTED && tries < 20) {
    delay(500);
    Serial.print(".");
    tries++;
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("Connected to university WiFi");
    mdisplay.clear();
    mdisplay.drawString(0, 0, "Connected to university WiFi");
    mdisplay.display();
    if (isCaptivePortal()) {
      loginToCaptivePortal(username, user_password, login_url);
    } else {
      Serial.println("Already authenticated!");
    }
  } else {
    Serial.println("Failed to connect to university WiFi");
    Serial.println("Retrying in 1 minute");
    mdisplay.clear();
    mdisplay.drawString(0, 0, "Failed to connect to university WiFi");
    mdisplay.drawString(0, 10, "Retrying in 1 minute");
    mdisplay.display();
    delay(1 * 60 * 1000);
    connectToUniversityWiFi();
  }
}

bool isCaptivePortal() {
  HTTPClient http;
  http.begin("http://clients3.google.com/generate_204");
  int httpCode = http.GET();
  http.end();
  return httpCode != 204;
}

void disconnectWiFi() {
  WiFi.disconnect(true);
  WiFi.mode(WIFI_OFF);
  Serial.println("Disconnected from WiFi");
}

void loginToCaptivePortal(const char* username, const char* user_password, const char* login_url) {
  client.setInsecure();
  HTTPClient https;
  https.begin(client, login_url);

  https.addHeader("Content-Type", "application/x-www-form-urlencoded");
  https.addHeader("Origin", "https://wlan.uom.lk");
  https.addHeader("Referer", "https://wlan.uom.lk/login.html?redirect=https://www.google.com/search");

  String postData = 
    "buttonClicked=4"
    "&err_flag=0"
    "&err_msg="
    "&info_flag=0"
    "&info_msg="
    "&redirect_url=https%3A%2F%2Fwww.google.com%2Fsearch"
    "&network_name=Guest+Network"
    "&username=" + String(username) +
    "&password=" + String(user_password);

  int httpResponseCode = https.POST(postData);

  if (httpResponseCode > 0) {
    Serial.printf("Login response code: %d\n", httpResponseCode);
    mdisplay.clear();
    mdisplay.drawString(0, 0, "Portal login successfull.");
    mdisplay.display();
  } else {
    Serial.printf("Login failed: %s\n", https.errorToString(httpResponseCode).c_str());
    mdisplay.clear();
    mdisplay.drawString(0, 0, "Portal login failed");
    mdisplay.drawString(0, 10, "retrying to login");
    mdisplay.display();
    delay(10000);
    if (isCaptivePortal()) {
      loginToCaptivePortal(username, user_password, login_url);
    } else {
      Serial.println("Already authenticated!");
    }
  }
  https.end();
}

// ======================= Data Upload & LoRa ======================

void sendDataToServer(float temp1, float temp2, float temp3, float humidity, float tempDHT, float distance, int bat, int rssi, int btrl) {
  stopHotspot();
  connectToUniversityWiFi();
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    String postData = "water_temp1=" + String(temp1) + "&water_temp2=" + String(temp2) +
                  "&water_temp3=" + String(temp3) + "&humidity=" + String(humidity) +
                  "&air_temp=" + String(tempDHT) + "&water_level=" + String(distance) +
                  "&battery_level=" + String(bat) + "&rssi=" + String(rssi) + "&btrl=" + String(btrl);

    int httpResponseCode = http.POST(postData);
    Serial.println("HTTP Response: " + String(httpResponseCode));
    if (httpResponseCode == 200) {
      mdisplay.drawString(0, 50, "Data uploaded successfully");
      mdisplay.display();
      Serial.println("Data uploaded successfully");
      delay(5000);
    } else {
      mdisplay.clear();
      mdisplay.drawString(0, 0, "Data upload failed");
      Serial.println("Data uploaded failed");
      mdisplay.drawString(0, 10, "Checking connections");
      mdisplay.display();
      Serial.println("Checking connections");
      if (WiFi.status() == WL_CONNECTED && isCaptivePortal()) {
        mdisplay.drawString(0, 20, "Captive portal detected");
        mdisplay.drawString(0, 30, "Login to portal");
        mdisplay.display();
        Serial.println("Captive portal detected. Login to portal");
        loginToCaptivePortal(username, user_password, login_url);
        sendDataToServer(temp1, temp2, temp3, humidity, tempDHT, distance, bat, rssi, btrl);
      } else {
        mdisplay.drawString(0, 20, "Connections are OK!");
        mdisplay.drawString(0, 30, "But data upload failed.");
        mdisplay.display();
        Serial.println("Connections are OK.");
        Serial.println("Data upload failed.");
        ESP.restart();
      }
    }
    http.end();
  } else {
    Serial.println("WiFi not connected!");
    mdisplay.clear();
    mdisplay.drawString(0, 0, "WiFi not connected!");
    mdisplay.display();
  }
  disconnectWiFi();
}

// ======================= Utility ================================

void checkConnection() {
  mdisplay.clear();
  mdisplay.drawString(0, 0, "Checking connections...");
  mdisplay.display();
  connectToUniversityWiFi();
  if (WiFi.status() == WL_CONNECTED) {
    mdisplay.clear();
    mdisplay.drawString(0, 0, "Connections are OK");
    mdisplay.display();
  }
  disconnectWiFi();
}

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

// ======================= Main Program ===========================

void setup() {
  Serial.begin(115200);

  // Load saved settings
  preferences.begin("blimas", true);
  String ap_pwd = preferences.getString("ap_password", ap_password);
  strncpy(ap_password, ap_pwd.c_str(), sizeof(ap_password) - 1);
  ap_password[sizeof(ap_password) - 1] = '\0';

  String user = preferences.getString("username", username);
  strncpy(username, user.c_str(), sizeof(username) - 1);
  username[sizeof(username) - 1] = '\0';

  String user_pwd = preferences.getString("user_password", user_password);
  strncpy(user_password, user_pwd.c_str(), sizeof(user_password) - 1);
  user_password[sizeof(user_password) - 1] = '\0';

  RF_FREQUENCY = preferences.getLong("freq", RF_FREQUENCY_DEFAULT);
  LORA_BANDWIDTH = preferences.getInt("bandwidth", LORA_BANDWIDTH);
  LORA_SPREADING_FACTOR = preferences.getInt("sf", LORA_SPREADING_FACTOR);

  String srv = preferences.getString("serverURL", serverURL);
  strncpy(serverURL, srv.c_str(), sizeof(serverURL) - 1);
  serverURL[sizeof(serverURL) - 1] = '\0';

  preferences.end();

  mdisplay.init();
  mdisplay.clear();
  mdisplay.drawString(0, 0, "Blimas Receiver");
  int btrl = readBatteryVoltage();
  mdisplay.drawString(0, 20, "Batt RL: " + String(btrl) + "%");
  mdisplay.display();
  mdisplay.display();
  //setupHotspot();
  //setupWebServer();
  Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);
  RadioEvents.RxDone = OnRxDone;
  Radio.Init(&RadioEvents);
  Radio.SetChannel(RF_FREQUENCY);
  Radio.SetRxConfig(MODEM_LORA, LORA_BANDWIDTH, LORA_SPREADING_FACTOR,
                    LORA_CODINGRATE, 0, 8, 0, false,
                    0, true, 0, 0, false, true);
  lora_idle = true;
  pinMode(LED ,OUTPUT);
  digitalWrite(LED, LOW);

  pinMode(ADC_Ctrl,OUTPUT);
  pinMode(VBAT_Read,INPUT);
  //adcAttachPin(VBAT_Read);
  analogReadResolution(12);
}

void loop() {
  if (lora_idle) {
    lora_idle = false;
    Radio.Rx(0);
  }
  Radio.IrqProcess();
  server.handleClient();
}

// ======================= LoRa Packet Handler ====================
void OnRxDone(uint8_t* payload, uint16_t size, int16_t _rssi, int8_t snr) {
  digitalWrite(LED, HIGH);
  rssi = _rssi;
  memcpy(rxpacket, payload, size);
  rxpacket[size] = 0;
  Radio.Sleep();
  Serial.printf("%s\n", rxpacket);
  if (strncmp(rxpacket, "LM|3552|", 8) == 0) {
    sscanf(rxpacket, "LM|3552|T1:%f,T2:%f,T3:%f,AirT:%f,H:%f,W:%f,B:%d",
           &temp1, &temp2, &temp3, &tempDHT, &humidity, &distance, &bat);
    Serial.printf("%d\n", bat);
    mdisplay.clear();
    mdisplay.drawString(0, 0, "Data received");
    mdisplay.drawString(0, 10, "RSSI: " + String(rssi) + " dBm");
    mdisplay.display();
    delay(1000);
    int btrl = readBatteryVoltage();
    sendDataToServer(temp1, temp2, temp3, humidity, tempDHT, distance, bat, rssi, btrl);
    lora_idle = true;
    digitalWrite(LED, LOW);
    Serial.printf("Going to deep sleep");
    delay(1000);
    esp_sleep_enable_timer_wakeup(sleepTime * 60 * 1000000);
    esp_deep_sleep_start();
  } else {
    Serial.println("Invalid LoRa packet received");
    lora_idle = true;
    digitalWrite(LED, LOW);
  }
}