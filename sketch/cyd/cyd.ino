#include <lvgl.h>
#include <TFT_eSPI.h>
#include <XPT2046_Touchscreen.h>
#include <WiFi.h>
#include <time.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>

// WiFi credentials
const char* ssid = "UoM_Wireless";
const char* password = "";
char username[64] = "";
char user_password[64] = "";
char login_url[128] = "https://wlan.uom.lk/login.html";

WiFiClientSecure client;

// TFT and Touchscreen
TFT_eSPI tft = TFT_eSPI();

#define XPT2046_IRQ 36
#define XPT2046_MOSI 32
#define XPT2046_MISO 39
#define XPT2046_CLK 25
#define XPT2046_CS 33

SPIClass touchscreenSPI = SPIClass(VSPI);
XPT2046_Touchscreen touchscreen(XPT2046_CS, XPT2046_IRQ);

// Display size and buffer
#define SCREEN_WIDTH  240
#define SCREEN_HEIGHT 320
#define DRAW_BUF_SIZE (SCREEN_WIDTH * SCREEN_HEIGHT / 10 * (LV_COLOR_DEPTH / 8))
uint32_t draw_buf[DRAW_BUF_SIZE / 4];

// RGB LED Pins
#define CYD_LED_BLUE 17
#define CYD_LED_RED 4
#define CYD_LED_GREEN 16

// LVGL labels
lv_obj_t *timeLabel;
lv_obj_t *greetingLabel;
lv_obj_t *airTempLabel, *humidityLabel, *waterLevelLabel;
lv_obj_t *wt1Label, *wt2Label, *wt3Label;

// Image headers
#include <hum.h>
#include <wl.h>
#include <wt.h>
#include <clk.h>
#include <tmp.h>
#include <bat.h>
#include <wifcon.h>
#include <pwr.h>

// WiFi connection
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
  } else {
    Serial.printf("Login failed: %s\n", https.errorToString(httpResponseCode).c_str());
    delay(10000);
    if (isCaptivePortal()) {
      loginToCaptivePortal(username, user_password, login_url);
    } else {
      Serial.println("Already authenticated!");
    }
  }
  https.end();
}

bool isCaptivePortal() {
  HTTPClient http;
  http.begin("http://clients3.google.com/generate_204");
  int httpCode = http.GET();
  http.end();
  return httpCode != 204;
}

void connectToWiFi() {
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi!");
  if (isCaptivePortal()) {
      loginToCaptivePortal(username, user_password, login_url);
    } else {
      Serial.println("Already authenticated!");
    }
}

// NTP time sync
void setupTime() {
  configTime(19800, 0, "pool.ntp.org");  // UTC+5:30
  struct tm timeinfo;
  while (!getLocalTime(&timeinfo)) {
    Serial.println("Waiting for time sync...");
    delay(1000);
  }
}

// Drawing helper functions
void drawImageHum(int x, int y) {
  LV_IMAGE_DECLARE(hum);
  lv_obj_t * img1 = lv_image_create(lv_screen_active());
  lv_image_set_src(img1, &hum);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}
void drawImageWl(int x, int y) {
  LV_IMAGE_DECLARE(wl);
  lv_obj_t * img1 = lv_image_create(lv_screen_active());
  lv_image_set_src(img1, &wl);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}
void drawImageWt(int x, int y) {
  LV_IMAGE_DECLARE(wt);
  lv_obj_t * img1 = lv_image_create(lv_screen_active());
  lv_image_set_src(img1, &wt);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}
void drawImageClk(int x, int y) {
  LV_IMAGE_DECLARE(clk);
  lv_obj_t * img1 = lv_image_create(lv_screen_active());
  lv_image_set_src(img1, &clk);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}
void drawImageTmp(int x, int y) {
  LV_IMAGE_DECLARE(tmp);
  lv_obj_t * img1 = lv_image_create(lv_screen_active());
  lv_image_set_src(img1, &tmp);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}
void drawImageBat(int x, int y) {
  LV_IMAGE_DECLARE(bat);
  lv_obj_t * img1 = lv_image_create(lv_screen_active());
  lv_image_set_src(img1, &bat);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}
void drawImageWifi(int x, int y) {
  LV_IMAGE_DECLARE(wifcon);
  lv_obj_t * img1 = lv_image_create(lv_screen_active());
  lv_image_set_src(img1, &wifcon);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}
void drawImagepwr(int x, int y) {
  LV_IMAGE_DECLARE(pwr);
  lv_obj_t * img1 = lv_image_create(lv_screen_active());
  lv_image_set_src(img1, &pwr);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void textWrite(int x, int y, const char * text, const lv_font_t *font = &lv_font_montserrat_12, int r=0, int g=0, int b=0) {
  lv_obj_t * label = lv_label_create(lv_screen_active());
  lv_label_set_text(label, text);
  lv_obj_set_style_text_font(label, font, 0);
  lv_obj_set_style_text_color(label, lv_color_make(r,g,b), 0);
  lv_obj_align(label, LV_ALIGN_TOP_LEFT, x, y);
}

void createSquare(int x, int y, int width, int height, int r, int g, int b) {
  lv_obj_t * square = lv_obj_create(lv_scr_act());
  lv_obj_set_size(square, width, height);
  lv_obj_set_style_border_color(square, lv_color_make(r,g,b), 0);
  lv_obj_set_style_radius(square, 0, LV_PART_MAIN | LV_STATE_DEFAULT);
  lv_obj_set_style_bg_color(square, lv_color_make(r,g,b), 0);
  lv_obj_align(square, LV_ALIGN_TOP_LEFT, x, y);
}

// Button callbacks
int btn1_count = 0;
static void event_handler_btn1(lv_event_t * e) {
  lv_event_code_t code = lv_event_get_code(e);
  if(code == LV_EVENT_CLICKED) {
    btn1_count++;
    LV_LOG_USER("Button clicked %d", (int)btn1_count);
    textWrite(0, 0, "Button clicked", &lv_font_montserrat_12, 255, 255, 255);
  }
}

void button1() {
  lv_obj_t * btn_label;
  lv_obj_t * btn1 = lv_button_create(lv_screen_active());
  lv_obj_add_event_cb(btn1, event_handler_btn1, LV_EVENT_ALL, NULL);
  lv_obj_align(btn1, LV_ALIGN_TOP_LEFT, 129, 5);
  lv_obj_remove_flag(btn1, LV_OBJ_FLAG_PRESS_LOCK);
  lv_obj_set_size(btn1, 61, 26);
  lv_obj_set_style_bg_color(btn1, lv_color_make(57,57,57), 0);
  btn_label = lv_label_create(btn1);
  lv_obj_set_style_text_color(btn_label, lv_color_make(255,165,0), 0);
  lv_label_set_text(btn_label, "Home");
  lv_obj_center(btn_label);
}

void button2() {
  lv_obj_t * btn_label;
  lv_obj_t * btn1 = lv_button_create(lv_screen_active());
  lv_obj_add_event_cb(btn1, event_handler_btn1, LV_EVENT_ALL, NULL);
  lv_obj_align(btn1, LV_ALIGN_TOP_LEFT, 193, 5);
  lv_obj_remove_flag(btn1, LV_OBJ_FLAG_PRESS_LOCK);
  lv_obj_set_size(btn1, 61, 26);
  lv_obj_set_style_bg_color(btn1, lv_color_make(255,255,255), 0);
  btn_label = lv_label_create(btn1);
  lv_obj_set_style_text_color(btn_label, lv_color_make(255,165,0), 0);
  lv_label_set_text(btn_label, "Data");
  lv_obj_center(btn_label);
}

void button3() {
  lv_obj_t * btn_label;
  lv_obj_t * btn1 = lv_button_create(lv_screen_active());
  lv_obj_add_event_cb(btn1, event_handler_btn1, LV_EVENT_ALL, NULL);
  lv_obj_align(btn1, LV_ALIGN_TOP_LEFT, 256, 5);
  lv_obj_remove_flag(btn1, LV_OBJ_FLAG_PRESS_LOCK);
  lv_obj_set_size(btn1, 61, 26);
  lv_obj_set_style_bg_color(btn1, lv_color_make(255,255,255), 0);
  btn_label = lv_label_create(btn1);
  lv_obj_set_style_text_color(btn_label, lv_color_make(255,165,0), 0);
  lv_label_set_text(btn_label, "Info");
  lv_obj_center(btn_label);
}


void fetchData() {
  HTTPClient http;
  http.begin("https://blimas.site/cyd.php"); // 🔁 Replace with actual URL
  int httpCode = http.GET();

  if (httpCode == 200) {
    String payload = http.getString();
    StaticJsonDocument<512> doc;
    DeserializationError error = deserializeJson(doc, payload);
    if (!error) {
      float airTemp = doc["air_temp"];
      float wt1 = doc["water_temp1"];
      float wt2 = doc["water_temp2"];
      float wt3 = doc["water_temp3"];
      float humidity = doc["humidity"];
      float waterLevel = doc["water_level"];

      char buffer[32];
      sprintf(buffer, "%.2f°C", airTemp);
      lv_label_set_text(airTempLabel, buffer);

      sprintf(buffer, "%.1f%%", humidity);
      lv_label_set_text(humidityLabel, buffer);

      sprintf(buffer, "%.1f cm", waterLevel);
      lv_label_set_text(waterLevelLabel, buffer);

      sprintf(buffer, "Surface - %.2f°C", wt1);
      lv_label_set_text(wt1Label, buffer);

      sprintf(buffer, "Mid         - %.2f°C", wt2);
      lv_label_set_text(wt2Label, buffer);

      sprintf(buffer, "Abyss    - %.2f°C", wt3);
      lv_label_set_text(wt3Label, buffer);
    }
  }

  http.end();
}


void setup() {
  Serial.begin(115200);
  connectToWiFi();
  setupTime();

  lv_init();
  lv_display_t * disp = lv_tft_espi_create(SCREEN_WIDTH, SCREEN_HEIGHT, draw_buf, sizeof(draw_buf));
  lv_display_set_rotation(disp, LV_DISPLAY_ROTATION_270);

  createSquare(0, 0, 320, 36, 0, 0, 0);
  createSquare(0, 222, 320, 18, 217,217,217);

  drawImageHum(230, 78);
  drawImageWl(129, 78);
  drawImageClk(226, 142);
  drawImageTmp(31, 78);
  drawImageWt(26, 163);
  drawImageBat(22, 226);
  drawImageWifi(5, 224);
  drawImagepwr(303, 224);

  textWrite(25, 7, "BLIMAS", &lv_font_montserrat_20, 255, 255, 255);
  textWrite(239, 105, "Humidity");
  textWrite(129, 105, "Water Level");
  textWrite(30, 105, "Temperature");
  textWrite(18, 134, "Water Temperature", &lv_font_montserrat_16);
  //textWrite(55, 82, "28.5°C");
  //textWrite(160, 82, "110 cm");
  //textWrite(258, 82, "76%");
  //textWrite(18, 45, "Good Morning !", &lv_font_montserrat_16);
  //textWrite(65, 159, "Surface - 27°c");
  //textWrite(65, 172, "Mid         - 27°c");
  //textWrite(65, 186, "Abyss    - 27°c");
  textWrite(107, 223, "Bolgoda Lake - Katubedda", &lv_font_montserrat_14);

  airTempLabel = lv_label_create(lv_screen_active());
  lv_obj_align(airTempLabel, LV_ALIGN_TOP_LEFT, 55, 82);

  waterLevelLabel = lv_label_create(lv_screen_active());
  lv_obj_align(waterLevelLabel, LV_ALIGN_TOP_LEFT, 160, 82);

  humidityLabel = lv_label_create(lv_screen_active());
  lv_obj_align(humidityLabel, LV_ALIGN_TOP_LEFT, 258, 82);

  wt1Label = lv_label_create(lv_screen_active());
  lv_obj_align(wt1Label, LV_ALIGN_TOP_LEFT, 65, 159);
  lv_obj_set_style_text_font(wt1Label, &lv_font_montserrat_12, 0);

  wt2Label = lv_label_create(lv_screen_active());
  lv_obj_align(wt2Label, LV_ALIGN_TOP_LEFT, 65, 172);
  lv_obj_set_style_text_font(wt2Label, &lv_font_montserrat_12, 0);

  wt3Label = lv_label_create(lv_screen_active());
  lv_obj_align(wt3Label, LV_ALIGN_TOP_LEFT, 65, 186);
  lv_obj_set_style_text_font(wt3Label, &lv_font_montserrat_12, 0);

  button1();
  button2();
  button3();

  // Create clock label
  timeLabel = lv_label_create(lv_screen_active());
  lv_obj_set_style_text_font(timeLabel, &lv_font_montserrat_20, 0);
  lv_obj_set_style_text_color(timeLabel, lv_color_make(0, 0, 0), 0);
  lv_obj_align(timeLabel, LV_ALIGN_TOP_LEFT, 200, 189);

  greetingLabel = lv_label_create(lv_screen_active());
  lv_obj_set_style_text_font(greetingLabel, &lv_font_montserrat_16, 0);
  lv_obj_set_style_text_color(greetingLabel, lv_color_make(0, 0, 0), 0);
  lv_obj_align(greetingLabel, LV_ALIGN_TOP_LEFT, 18, 45);


  digitalWrite(CYD_LED_GREEN, HIGH);
  digitalWrite(CYD_LED_BLUE, HIGH);
  digitalWrite(CYD_LED_RED, HIGH);
}

void loop() {
  static unsigned long lastUpdate = 0;

  if (millis() - lastUpdate > 1000) {
    lastUpdate = millis();
    struct tm timeinfo;
    if (getLocalTime(&timeinfo)) {
      char timeStr[16];
      strftime(timeStr, sizeof(timeStr), "%I:%M %p", &timeinfo);  // 12-hour format
      lv_label_set_text(timeLabel, timeStr);
    }
    int currentHour = timeinfo.tm_hour;
    const char* greeting;

    if (currentHour >= 5 && currentHour < 12) {
        greeting = "Good Morning!";
    } else if (currentHour >= 12 && currentHour < 17) {
        greeting = "Good Afternoon!";
    } else if (currentHour >= 17 && currentHour < 21) {
        greeting = "Good Evening!";
    } else {
        greeting = "Good Night!";
    }
    lv_label_set_text(greetingLabel, greeting);

    fetchData();
  }
  

  lv_task_handler();
  lv_tick_inc(5);
  delay(5);
}
