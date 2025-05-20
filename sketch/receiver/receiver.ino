// blimas receiver with local web server, config, OTA

#include "LoRaWan_APP.h"
#include "Arduino.h"
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include <ESPmDNS.h>
#include <Update.h>
#include "HT_SSD1306Wire.h"

// Wi-Fi credentials
char ssid[32] = "UoM_Wireless";
char password[64] = "";

// Login info
char login_url[128] = "https://wlan.uom.lk/login.html";
char username[64] = "";
char user_password[64] = "";

char serverURL[128] = "https://webpojja.pasgorasa.site/upload.php";

WiFiClientSecure client;
WebServer server(80);
SSD1306Wire mdisplay(0x3c, 500000, SDA_OLED, SCL_OLED, GEOMETRY_128_64, RST_OLED);

#define RF_FREQUENCY 433300000
#define TX_OUTPUT_POWER 14
int LORA_SPREADING_FACTOR = 7;
int LORA_BANDWIDTH = 0;
int LORA_CODINGRATE = 1;

#define BUFFER_SIZE 128
char rxpacket[BUFFER_SIZE];
bool lora_idle = true;
int16_t rssi;

static RadioEvents_t RadioEvents;

float temp1, temp2, temp3, humidity, tempDHT, distance;

// Web page for live display
String htmlPage() {
  String html = "<html><head><meta http-equiv='refresh' content='5'/>";
  html += "<title>BLIMAS Receiver</title></head><body>";
  html += "<h2>Real-Time Sensor Data</h2>";
  html += "<p>Air Temp: " + String(tempDHT) + " °C</p>";
  html += "<p>Humidity: " + String(humidity) + " %</p>";
  html += "<p>Water Level: " + String(distance) + " cm</p>";
  html += "<p>Water Temps: " + String(temp1) + ", " + String(temp2) + ", " + String(temp3) + " °C</p>";
  html += "<p>RSSI: " + String(rssi) + " dBm</p>";
  html += "<a href='/config'>Configure</a> | <a href='/update'>OTA Update</a>";
  html += "</body></html>";
  return html;
}

// Configuration form
String configPage() {
  String html = "<html><body><h2>Receiver Configuration</h2>";
  html += "<form method='POST' action='/save'>";
  html += "SSID: <input name='ssid' value='" + String(ssid) + "'><br>";
  html += "Password: <input name='password' value='" + String(password) + "'><br>";
  html += "Username: <input name='username' value='" + String(username) + "'><br>";
  html += "User Password: <input name='user_password' value='" + String(user_password) + "'><br>";
  html += "Server URL: <input name='serverURL' value='" + String(serverURL) + "'><br>";
  html += "LoRa SF (7-12): <input name='sf' value='" + String(LORA_SPREADING_FACTOR) + "'><br>";
  html += "<input type='submit' value='Save'>";
  html += "</form><br><a href='/'>Back</a></body></html>";
  return html;
}

void handleRoot() {
  server.send(200, "text/html", htmlPage());
}

void handleConfig() {
  server.send(200, "text/html", configPage());
}

void handleSave() {
  if (server.hasArg("ssid")) strncpy(ssid, server.arg("ssid").c_str(), sizeof(ssid));
  if (server.hasArg("password")) strncpy(password, server.arg("password").c_str(), sizeof(password));
  if (server.hasArg("username")) strncpy(username, server.arg("username").c_str(), sizeof(username));
  if (server.hasArg("user_password")) strncpy(user_password, server.arg("user_password").c_str(), sizeof(user_password));
  if (server.hasArg("serverURL")) strncpy(serverURL, server.arg("serverURL").c_str(), sizeof(serverURL));
  if (server.hasArg("sf")) LORA_SPREADING_FACTOR = server.arg("sf").toInt();
  server.send(200, "text/html", "<html><body><h2>Saved. Restarting...</h2><script>setTimeout(()=>location.href='/', 2000);</script></body></html>");
  delay(2000);
  ESP.restart();
}

void setupWebServer() {
  server.on("/", handleRoot);
  server.on("/config", handleConfig);
  server.on("/save", HTTP_POST, handleSave);
  server.begin();
  Serial.println("HTTP server started");
}

// Wi-Fi connect + OLED init
void setup() {
  Serial.begin(115200);
  mdisplay.init();
  mdisplay.clear();
  mdisplay.drawString(0, 0, "Connecting WiFi...");
  mdisplay.display();

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("WiFi connected");

  mdisplay.clear();
  mdisplay.drawString(0, 0, "WiFi connected");
  mdisplay.drawString(0, 10, WiFi.localIP().toString());
  mdisplay.display();

  if (!MDNS.begin("blimas")) {
    Serial.println("Error setting up MDNS");
  }

  setupWebServer();
  Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);
  RadioEvents.RxDone = OnRxDone;
  Radio.Init(&RadioEvents);
  Radio.SetChannel(RF_FREQUENCY);
  Radio.SetRxConfig(MODEM_LORA, LORA_BANDWIDTH, LORA_SPREADING_FACTOR,
                    LORA_CODINGRATE, 0, 8, 0, false,
                    0, true, 0, 0, false, true);
  lora_idle = true;
}

void loop() {
  if (lora_idle) {
    lora_idle = false;
    Radio.Rx(0);
  }
  Radio.IrqProcess();
  server.handleClient();
}

void OnRxDone(uint8_t* payload, uint16_t size, int16_t _rssi, int8_t snr) {
  rssi = _rssi;
  memcpy(rxpacket, payload, size);
  rxpacket[size] = 0;
  Radio.Sleep();

  sscanf(rxpacket, "LM|3552|T1:%f,T2:%f,T3:%f,AirT:%f,H:%f,W:%f",
         &temp1, &temp2, &temp3, &tempDHT, &humidity, &distance);

  mdisplay.clear();
  mdisplay.drawString(0, 0, "Lake Monitor - RX");
  mdisplay.drawString(0, 10, "T1:" + String(temp1) + ", T2:" + String(temp2));
  mdisplay.drawString(0, 20, "T3:" + String(temp3) + ", AT:" + String(tempDHT));
  mdisplay.drawString(0, 30, "H:" + String(humidity) + ", W:" + String(distance));
  mdisplay.drawString(0, 40, "RSSI: " + String(rssi));
  mdisplay.display();
  lora_idle = true;
}
