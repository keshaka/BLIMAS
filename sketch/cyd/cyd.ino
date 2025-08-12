#include <lvgl.h>
#include <TFT_eSPI.h>
#include <XPT2046_Touchscreen.h>
#include <WiFi.h>
#include <time.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>
#include <SPI.h>

#define XPT2046_IRQ 36   // T_IRQ
#define XPT2046_MOSI 32  // T_DIN
#define XPT2046_MISO 39  // T_OUT
#define XPT2046_CLK 25   // T_CLK
#define XPT2046_CS 33    // T_CS

SPIClass touchscreenSPI = SPIClass(VSPI);
XPT2046_Touchscreen touchscreen(XPT2046_CS, XPT2046_IRQ);

#define SCREEN_WIDTH 240
#define SCREEN_HEIGHT 320
#define DRAW_BUF_SIZE (SCREEN_WIDTH * SCREEN_HEIGHT / 10 * (LV_COLOR_DEPTH / 8))
uint32_t draw_buf[DRAW_BUF_SIZE / 4];

int x, y, z;

static lv_obj_t * data_screen = NULL;
static lv_obj_t * main_screen = NULL;
static lv_obj_t * info_screen;
static lv_obj_t * data_content_container;

lv_obj_t *timeLabel;
lv_obj_t *greetingLabel;
lv_obj_t *airTempLabel, *humidityLabel, *waterLevelLabel;
lv_obj_t *wt1Label, *wt2Label, *wt3Label;

lv_obj_t *air_avg_label, *air_max_label, *air_min_label;
lv_obj_t *hum_avg_label, *hum_max_label, *hum_min_label;
lv_obj_t *wl_avg_label, *wl_max_label, *wl_min_label;
lv_obj_t *wt1_avg_label, *wt1_max_label, *wt1_min_label;
lv_obj_t *wt2_avg_label, *wt2_max_label, *wt2_min_label;
lv_obj_t *wt3_avg_label, *wt3_max_label, *wt3_min_label;

lv_obj_t * loading_screen = NULL;
lv_obj_t * loading_label = NULL;
lv_obj_t * progress_bar = NULL;

// Image headers
#include <hum.h>
#include <wl.h>
#include <wt.h>
#include <clk.h>
#include <tmp.h>
#include <bat.h>
#include <wifcon.h>
#include <pwr.h>

// WiFi credentials
const char* ssid = "UoM_Wireless";
const char* password = "";
char username[64] = "wijerathnarktp.23";
char user_password[64] = "Tharusha@2003";
char login_url[128] = "https://wlan.uom.lk/login.html";

WiFiClientSecure client;

// RGB LED Pins
#define CYD_LED_BLUE 17
#define CYD_LED_RED 4
#define CYD_LED_GREEN 16
#define LDR_PIN 34
#define BACKLIGHT_PIN 21

void updateTimeAndGreeting(lv_timer_t * timer) {
  struct tm timeinfo;
  if (getLocalTime(&timeinfo)) {
    // Update time display
    char timeStr[16];
    strftime(timeStr, sizeof(timeStr), "%I:%M %p", &timeinfo);  // 12-hour format
    lv_label_set_text(timeLabel, timeStr);
    
    // Update greeting based on current hour
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
  }
}

void setBrightnessLVGL(uint8_t brightness) {
  // Direct PWM control
  ledcWrite(BACKLIGHT_PIN, brightness);
}

void setupAutoBrightness() {
  ledcAttach(BACKLIGHT_PIN, 5000, 8);
  setBrightnessLVGL(128);
  pinMode(LDR_PIN, INPUT);
  Serial.println("Auto brightness setup complete");
}

void autoBrightnessTimer(lv_timer_t * timer) {
  int ldrValue = analogRead(LDR_PIN);
  int brightness;
  
  if (ldrValue < 50) {          // Very bright light
    brightness = 255;
  } else if (ldrValue < 150) {  // Bright light
    brightness = 200;
  } else if (ldrValue < 250) {  // Medium light
    brightness = 120;
  } else {                      // Low light
    brightness = 60;
  }
  
  static int smoothedBrightness = 128;
  smoothedBrightness = (smoothedBrightness * 3 + brightness) / 4;
  smoothedBrightness = constrain(smoothedBrightness, 30, 255);
  
  setBrightnessLVGL(smoothedBrightness);
  
  Serial.printf("Auto brightness: LDR=%d, Target=%d, Applied=%d\n", 
                ldrValue, brightness, smoothedBrightness);
}

void create_loading_screen() {
  loading_screen = lv_obj_create(NULL);
  lv_obj_set_style_bg_color(loading_screen, lv_color_hex(0x1e1e1e), LV_PART_MAIN);
  
  // BLIMAS logo
  lv_obj_t * logo = lv_label_create(loading_screen);
  lv_label_set_text(logo, "BLIMAS");
  lv_obj_set_style_text_font(logo, &lv_font_montserrat_24, LV_PART_MAIN);
  lv_obj_set_style_text_color(logo, lv_color_white(), LV_PART_MAIN);
  lv_obj_align(logo, LV_ALIGN_CENTER, 0, -60);
  
  // Loading label
  loading_label = lv_label_create(loading_screen);
  lv_label_set_text(loading_label, "Initializing...");
  lv_obj_set_style_text_color(loading_label, lv_color_hex(0xCCCCCC), LV_PART_MAIN);
  lv_obj_set_style_text_font(loading_label, &lv_font_montserrat_16, LV_PART_MAIN);
  lv_obj_align(loading_label, LV_ALIGN_CENTER, 0, 0);
  
  // Progress bar
  progress_bar = lv_bar_create(loading_screen);
  lv_obj_set_size(progress_bar, 200, 10);
  lv_obj_align(progress_bar, LV_ALIGN_CENTER, 0, 40);
  lv_obj_set_style_bg_color(progress_bar, lv_color_hex(0x444444), LV_PART_MAIN);
  lv_obj_set_style_bg_color(progress_bar, lv_color_hex(0x2196F3), LV_PART_INDICATOR);
  
  lv_scr_load(loading_screen);
}

void update_loading_progress(const char* message, int progress) {
  if (loading_label) {
    lv_label_set_text(loading_label, message);
  }
  if (progress_bar) {
    lv_bar_set_value(progress_bar, progress, LV_ANIM_ON);
  }
  lv_timer_handler();
  lv_refr_now(NULL); 
  delay(500);
}

// WiFi connection
void loginToCaptivePortal(const char* username, const char* user_password, const char* login_url) {
  update_loading_progress("Login to WIfi portal", 55);
  lv_scr_load(loading_screen);
  client.setInsecure();
  HTTPClient https;
  https.begin(client, login_url);

  https.addHeader("User-Agent", "Mozilla/5.0 (ESP32; Heltec LoRa32 V3)");
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
    digitalWrite(CYD_LED_BLUE, HIGH);
  } else {
    Serial.printf("Login failed: %s\n", https.errorToString(httpResponseCode).c_str());
    delay(10000);
    if (isCaptivePortal()) {
      loginToCaptivePortal(username, user_password, login_url);
    } else {
      Serial.println("Already authenticated!");
      digitalWrite(CYD_LED_BLUE, HIGH);
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
  digitalWrite(CYD_LED_GREEN, LOW);
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi!");
  digitalWrite(CYD_LED_GREEN, HIGH);
  if (isCaptivePortal()) {
      digitalWrite(CYD_LED_BLUE, LOW);
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
void drawImageHum(lv_obj_t * parent, int x, int y) {
  LV_IMAGE_DECLARE(hum);
  lv_obj_t * img1 = lv_image_create(parent);
  lv_image_set_src(img1, &hum);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void drawImageWl(lv_obj_t * parent, int x, int y) {
  LV_IMAGE_DECLARE(wl);
  lv_obj_t * img1 = lv_image_create(parent);
  lv_image_set_src(img1, &wl);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void drawImageWt(lv_obj_t * parent, int x, int y) {
  LV_IMAGE_DECLARE(wt);
  lv_obj_t * img1 = lv_image_create(parent);
  lv_image_set_src(img1, &wt);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void drawImageClk(lv_obj_t * parent, int x, int y) {
  LV_IMAGE_DECLARE(clk);
  lv_obj_t * img1 = lv_image_create(parent);
  lv_image_set_src(img1, &clk);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void drawImageTmp(lv_obj_t * parent, int x, int y) {
  LV_IMAGE_DECLARE(tmp);
  lv_obj_t * img1 = lv_image_create(parent);
  lv_image_set_src(img1, &tmp);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void drawImageBat(lv_obj_t * parent, int x, int y) {
  LV_IMAGE_DECLARE(bat);
  lv_obj_t * img1 = lv_image_create(parent);
  lv_image_set_src(img1, &bat);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void drawImageWifi(lv_obj_t * parent, int x, int y) {
  LV_IMAGE_DECLARE(wifcon);
  lv_obj_t * img1 = lv_image_create(parent);
  lv_image_set_src(img1, &wifcon);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void drawImagepwr(lv_obj_t * parent, int x, int y) {
  LV_IMAGE_DECLARE(pwr);
  lv_obj_t * img1 = lv_image_create(parent);
  lv_image_set_src(img1, &pwr);
  lv_obj_align(img1, LV_ALIGN_TOP_LEFT, x, y);
}

void textWrite(lv_obj_t * parent, int x, int y, const char * text, const lv_font_t *font = &lv_font_montserrat_12, int r=0, int g=0, int b=0) {
  lv_obj_t * label = lv_label_create(parent);
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

void log_print(lv_log_level_t level, const char * buf) {
  LV_UNUSED(level);
  Serial.println(buf);
  Serial.flush();
}

void fetchData(lv_timer_t * timer) {
  if (isCaptivePortal()) {
      if (WiFi.status() != WL_CONNECTED) {
        connectToWiFi();
      }
  }
  HTTPClient http;
  http.begin("http://blimas.unilodge.live/cyd.php"); 
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

void fetch_daily_stats(lv_timer_t * timer) {
  if (WiFi.status() == WL_CONNECTED && data_screen != NULL) {
    HTTPClient http;
    http.begin("http://blimas.unilodge.live/daily_stats.php");
    int httpCode = http.GET();

    if (httpCode == 200) {
      String payload = http.getString();
      StaticJsonDocument<2048> doc;
      DeserializationError error = deserializeJson(doc, payload);

      if (!error) {
        Serial.println("Daily stats updated successfully");
        
        char buffer[32];
        
        if (air_avg_label != NULL) {
          sprintf(buffer, "Avg: %.1f°C", doc["air_temp"]["avg"].as<float>());
          lv_label_set_text(air_avg_label, buffer);
          
          sprintf(buffer, "Max: %.1f°C", doc["air_temp"]["max"].as<float>());
          lv_label_set_text(air_max_label, buffer);
          
          sprintf(buffer, "Min: %.1f°C", doc["air_temp"]["min"].as<float>());
          lv_label_set_text(air_min_label, buffer);
        }
        
        if (hum_avg_label != NULL) {
          sprintf(buffer, "Avg: %.1f%%", doc["humidity"]["avg"].as<float>());
          lv_label_set_text(hum_avg_label, buffer);
          
          sprintf(buffer, "Max: %.1f%%", doc["humidity"]["max"].as<float>());
          lv_label_set_text(hum_max_label, buffer);
          
          sprintf(buffer, "Min: %.1f%%", doc["humidity"]["min"].as<float>());
          lv_label_set_text(hum_min_label, buffer);
        }
        
        if (wl_avg_label != NULL) {
          sprintf(buffer, "Avg: %.1fcm", doc["water_level"]["avg"].as<float>());
          lv_label_set_text(wl_avg_label, buffer);
          
          sprintf(buffer, "Max: %.1fcm", doc["water_level"]["max"].as<float>());
          lv_label_set_text(wl_max_label, buffer);
          
          sprintf(buffer, "Min: %.1fcm", doc["water_level"]["min"].as<float>());
          lv_label_set_text(wl_min_label, buffer);
        }
        
        if (wt1_avg_label != NULL) {
          sprintf(buffer, "Avg: %.1f°C", doc["water_temp1"]["avg"].as<float>());
          lv_label_set_text(wt1_avg_label, buffer);
          
          sprintf(buffer, "Max: %.1f°C", doc["water_temp1"]["max"].as<float>());
          lv_label_set_text(wt1_max_label, buffer);
          
          sprintf(buffer, "Min: %.1f°C", doc["water_temp1"]["min"].as<float>());
          lv_label_set_text(wt1_min_label, buffer);
        }
        
        if (wt2_avg_label != NULL) {
          sprintf(buffer, "Avg: %.1f°C", doc["water_temp2"]["avg"].as<float>());
          lv_label_set_text(wt2_avg_label, buffer);
          
          sprintf(buffer, "Max: %.1f°C", doc["water_temp2"]["max"].as<float>());
          lv_label_set_text(wt2_max_label, buffer);
          
          sprintf(buffer, "Min: %.1f°C", doc["water_temp2"]["min"].as<float>());
          lv_label_set_text(wt2_min_label, buffer);
        }
        
        if (wt3_avg_label != NULL) {
          sprintf(buffer, "Avg: %.1f°C", doc["water_temp3"]["avg"].as<float>());
          lv_label_set_text(wt3_avg_label, buffer);
          
          sprintf(buffer, "Max: %.1f°C", doc["water_temp3"]["max"].as<float>());
          lv_label_set_text(wt3_max_label, buffer);
          
          sprintf(buffer, "Min: %.1f°C", doc["water_temp3"]["min"].as<float>());
          lv_label_set_text(wt3_min_label, buffer);
        }
        
        Serial.printf("Air Temp - Avg: %.1f, Max: %.1f, Min: %.1f\n", 
                     doc["air_temp"]["avg"].as<float>(),
                     doc["air_temp"]["max"].as<float>(),
                     doc["air_temp"]["min"].as<float>());
        
        Serial.printf("Status: %s, Records: %d\n", 
                     doc["status"].as<const char*>(),
                     doc["record_count"].as<int>());
        
      } else {
        Serial.println("JSON parsing failed for daily stats");
      }
    } else {
      Serial.printf("HTTP request failed for daily stats, code: %d\n", httpCode);
    }
    http.end();
  }
}

void create_data_card(lv_obj_t * parent, const char* title, const char* unit, int index, int y_pos, uint32_t color) {

  lv_obj_t * card = lv_obj_create(parent);
  lv_obj_set_size(card, 280, 100);
  lv_obj_align(card, LV_ALIGN_TOP_MID, 0, y_pos);
  lv_obj_set_style_bg_color(card, lv_color_white(), LV_PART_MAIN);
  lv_obj_set_style_border_width(card, 1, LV_PART_MAIN);
  lv_obj_set_style_border_color(card, lv_color_hex(0xe0e0e0), LV_PART_MAIN);
  lv_obj_set_style_radius(card, 8, LV_PART_MAIN);
  lv_obj_set_style_shadow_width(card, 3, LV_PART_MAIN);
  lv_obj_set_style_shadow_color(card, lv_color_hex(0x000000), LV_PART_MAIN);
  lv_obj_set_style_shadow_opa(card, LV_OPA_20, LV_PART_MAIN);

  lv_obj_t * title_label = lv_label_create(card);
  lv_label_set_text(title_label, title);
  lv_obj_set_style_text_color(title_label, lv_color_hex(color), LV_PART_MAIN);
  lv_obj_set_style_text_font(title_label, &lv_font_montserrat_16, LV_PART_MAIN);
  lv_obj_align(title_label, LV_ALIGN_TOP_LEFT, 10, 0);

  lv_obj_t * avg_label = lv_label_create(card);
  lv_label_set_text(avg_label, "Avg: --.-");
  lv_obj_set_style_text_color(avg_label, lv_color_hex(0x333333), LV_PART_MAIN);
  lv_obj_set_style_text_font(avg_label, &lv_font_montserrat_14, LV_PART_MAIN);
  lv_obj_align(avg_label, LV_ALIGN_TOP_LEFT, 10, 25);

  lv_obj_t * max_label = lv_label_create(card);
  lv_label_set_text(max_label, "Max: --.-");
  lv_obj_set_style_text_color(max_label, lv_color_hex(0xff5722), LV_PART_MAIN);
  lv_obj_align(max_label, LV_ALIGN_TOP_LEFT, 95, 50);

  lv_obj_t * min_label = lv_label_create(card);
  lv_label_set_text(min_label, "Min: --.-");
  lv_obj_set_style_text_color(min_label, lv_color_hex(0x2196f3), LV_PART_MAIN);
  lv_obj_align(min_label, LV_ALIGN_TOP_LEFT, 10, 50);

  switch(index) {
    case 0:
      air_avg_label = avg_label;
      air_max_label = max_label;
      air_min_label = min_label;
      break;
    case 1: 
      hum_avg_label = avg_label;
      hum_max_label = max_label;
      hum_min_label = min_label;
      break;
    case 2:
      wl_avg_label = avg_label;
      wl_max_label = max_label;
      wl_min_label = min_label;
      break;
    case 3: 
      wt1_avg_label = avg_label;
      wt1_max_label = max_label;
      wt1_min_label = min_label;
      break;
    case 4: 
      wt2_avg_label = avg_label;
      wt2_max_label = max_label;
      wt2_min_label = min_label;
      break;
    case 5: 
      wt3_avg_label = avg_label;
      wt3_max_label = max_label;
      wt3_min_label = min_label;
      break;
  }

  switch(index) {
    case 0: 
      drawImageTmp(card, 230, 10); 
      break;
    case 1: 
      drawImageHum(card, 230, 10);
      break;
    case 2: 
      drawImageWl(card, 225, 10);
      break;
    case 3: 
      drawImageWt(card, 220, 10);
      break;
    case 4: 
      drawImageWt(card, 220, 10);
      break;
    case 5:
      drawImageWt(card, 220, 10);
      break;
  }
}

void touchscreen_read(lv_indev_t * indev, lv_indev_data_t * data) {
  if(touchscreen.tirqTouched() && touchscreen.touched()) {
    TS_Point p = touchscreen.getPoint();
    x = map(p.x, 200, 3700, 1, SCREEN_WIDTH);
    y = map(p.y, 240, 3800, 1, SCREEN_HEIGHT);
    z = p.z;
    data->state = LV_INDEV_STATE_PRESSED;
    data->point.x = x;
    data->point.y = y;
  } else {
    data->state = LV_INDEV_STATE_RELEASED;
  }
}

static void event_handler_home_btn(lv_event_t * e) {
  if (lv_event_get_code(e) == LV_EVENT_CLICKED) {
  lv_scr_load(main_screen);
  }
}

static void event_handler_data_btn(lv_event_t * e) {
  if (lv_event_get_code(e) == LV_EVENT_CLICKED) {
    if (data_screen == NULL) {
      create_data_screen();
      fetch_daily_stats(NULL);
    }
    lv_scr_load(data_screen);
    lv_obj_scroll_to_y(data_content_container, 0, LV_ANIM_OFF);
  }
}

static void event_handler_refresh_btn(lv_event_t * e) {
  if (lv_event_get_code(e) == LV_EVENT_CLICKED) {
    lv_scr_load(info_screen);
    uint32_t child_count = lv_obj_get_child_count(info_screen);
    for(uint32_t i = 0; i < child_count; i++) {
      lv_obj_t * child = lv_obj_get_child(info_screen, i);
      if(lv_obj_has_flag(child, LV_OBJ_FLAG_SCROLLABLE) && !lv_obj_has_flag(child, LV_OBJ_FLAG_FLOATING)) {
        lv_obj_scroll_to_y(child, 0, LV_ANIM_OFF);
        break;
      }
    }
  }
}

void create_info_screen() {
  info_screen = lv_obj_create(NULL);
  lv_obj_set_style_bg_color(info_screen, lv_color_hex(0xf5f5f5), LV_PART_MAIN);

  lv_obj_t * content = lv_obj_create(info_screen);
  lv_obj_set_size(content, 310, 200);
  lv_obj_align(content, LV_ALIGN_CENTER, 0, 19);
  lv_obj_set_style_bg_color(content, lv_color_hex(0xffffff), LV_PART_MAIN);
  lv_obj_set_style_border_width(content, 1, LV_PART_MAIN);
  lv_obj_set_style_border_color(content, lv_color_hex(0xe0e0e0), LV_PART_MAIN);
  lv_obj_set_style_radius(content, 10, LV_PART_MAIN);
  lv_obj_set_scroll_dir(content, LV_DIR_VER);

  lv_obj_t * title = lv_label_create(content);
  lv_label_set_text(title, "BLIMAS");
  lv_obj_set_style_text_font(title, &lv_font_montserrat_24, LV_PART_MAIN);
  lv_obj_set_style_text_color(title, lv_color_hex(0x000000), LV_PART_MAIN);
  lv_obj_align(title, LV_ALIGN_TOP_MID, 0, 5);

  lv_obj_t * subtitle = lv_label_create(content);
  lv_label_set_text(subtitle, "Bolgoda Lake Information\nMonitoring & Analysis System");
  lv_obj_set_style_text_align(subtitle, LV_TEXT_ALIGN_CENTER, LV_PART_MAIN);
  lv_obj_set_style_text_color(subtitle, lv_color_hex(0x666666), LV_PART_MAIN);
  lv_obj_align(subtitle, LV_ALIGN_TOP_MID, 0, 45);

  lv_obj_t * line1 = lv_obj_create(content);
  lv_obj_set_size(line1, 250, 2);
  lv_obj_set_style_bg_color(line1, lv_color_hex(0xe0e0e0), LV_PART_MAIN);
  lv_obj_set_style_border_width(line1, 0, LV_PART_MAIN);
  lv_obj_align(line1, LV_ALIGN_TOP_MID, 0, 95);

  lv_obj_t * web_icon = lv_label_create(content);
  lv_label_set_text(web_icon, LV_SYMBOL_WIFI);
  lv_obj_set_style_text_color(web_icon, lv_color_hex(0x4caf50), LV_PART_MAIN);
  lv_obj_align(web_icon, LV_ALIGN_TOP_LEFT, 30, 115);

  lv_obj_t * web_label = lv_label_create(content);
  lv_label_set_text(web_label, "Website:");
  lv_obj_set_style_text_color(web_label, lv_color_hex(0x333333), LV_PART_MAIN);
  lv_obj_align(web_label, LV_ALIGN_TOP_LEFT, 60, 115);

  lv_obj_t * web_url = lv_label_create(content);
  lv_label_set_text(web_url, "www.blimas.site");
  lv_obj_set_style_text_color(web_url, lv_color_hex(0x2196f3), LV_PART_MAIN);
  lv_obj_align(web_url, LV_ALIGN_TOP_LEFT, 60, 135);

  lv_obj_t * bot_icon = lv_label_create(content);
  lv_label_set_text(bot_icon, LV_SYMBOL_CALL);
  lv_obj_set_style_text_color(bot_icon, lv_color_hex(0x4caf50), LV_PART_MAIN);
  lv_obj_align(bot_icon, LV_ALIGN_TOP_LEFT, 30, 165);

  lv_obj_t * bot_label = lv_label_create(content);
  lv_label_set_text(bot_label, "Telegram Bot:");
  lv_obj_set_style_text_color(bot_label, lv_color_hex(0x333333), LV_PART_MAIN);
  lv_obj_align(bot_label, LV_ALIGN_TOP_LEFT, 60, 165);

  lv_obj_t * bot_handle = lv_label_create(content);
  lv_label_set_text(bot_handle, "@blimas_bot");
  lv_obj_set_style_text_color(bot_handle, lv_color_hex(0x2196f3), LV_PART_MAIN);
  lv_obj_align(bot_handle, LV_ALIGN_TOP_LEFT, 60, 185);

  lv_obj_t * features_title = lv_label_create(content);
  lv_label_set_text(features_title, "Features:");
  lv_obj_set_style_text_color(features_title, lv_color_hex(0x333333), LV_PART_MAIN);
  lv_obj_set_style_text_font(features_title, &lv_font_montserrat_16, LV_PART_MAIN);
  lv_obj_align(features_title, LV_ALIGN_TOP_LEFT, 30, 215);

  lv_obj_t * features = lv_label_create(content);
  lv_label_set_text(features, 
    "• Real-time Data\n"
    "• Temperature tracking\n"
    "• Humidity measurements\n"
    "• Water level monitoring\n"
    "• Historical data charts\n"
    "• Remote access via web\n"
    "• Telegram notifications");
  lv_obj_set_style_text_color(features, lv_color_hex(0x666666), LV_PART_MAIN);
  lv_obj_align(features, LV_ALIGN_TOP_LEFT, 30, 240);

  lv_obj_t * line2 = lv_obj_create(content);
  lv_obj_set_size(line2, 250, 2);
  lv_obj_set_style_bg_color(line2, lv_color_hex(0xe0e0e0), LV_PART_MAIN);
  lv_obj_set_style_border_width(line2, 0, LV_PART_MAIN);
  lv_obj_align(line2, LV_ALIGN_TOP_MID, 0, 370);

  lv_obj_t * dev_icon = lv_label_create(content);
  lv_label_set_text(dev_icon, LV_SYMBOL_SETTINGS);
  lv_obj_set_style_text_color(dev_icon, lv_color_hex(0x000000), LV_PART_MAIN);
  lv_obj_align(dev_icon, LV_ALIGN_TOP_LEFT, 30, 390);

  lv_obj_t * dev_label = lv_label_create(content);
  lv_label_set_text(dev_label, "Developed by:");
  lv_obj_set_style_text_color(dev_label, lv_color_hex(0x333333), LV_PART_MAIN);
  lv_obj_align(dev_label, LV_ALIGN_TOP_LEFT, 60, 390);

  lv_obj_t * dev_name = lv_label_create(content);
  lv_label_set_text(dev_name, "Circuit Sages");
  lv_obj_set_style_text_color(dev_name, lv_color_hex(0x2196f3), LV_PART_MAIN);
  lv_obj_set_style_text_font(dev_name, &lv_font_montserrat_16, LV_PART_MAIN);
  lv_obj_align(dev_name, LV_ALIGN_TOP_LEFT, 60, 410);

  lv_obj_t * version = lv_label_create(content);
  lv_label_set_text(version, "Version 1.0 | ESP32 CYD");
  lv_obj_set_style_text_color(version, lv_color_hex(0x999999), LV_PART_MAIN);
  lv_obj_set_style_text_align(version, LV_TEXT_ALIGN_CENTER, LV_PART_MAIN);
  lv_obj_align(version, LV_ALIGN_TOP_MID, 0, 450);

  lv_obj_t * copyright = lv_label_create(content);
  lv_label_set_text(copyright, "2025 Circuit Sages");
  lv_obj_set_style_text_color(copyright, lv_color_hex(0x999999), LV_PART_MAIN);
  lv_obj_set_style_text_align(copyright, LV_TEXT_ALIGN_CENTER, LV_PART_MAIN);
  lv_obj_align(copyright, LV_ALIGN_TOP_MID, 0, 475);

  lv_obj_t * black_square = lv_obj_create(info_screen);
  lv_obj_set_size(black_square, 320, 36);
  lv_obj_set_style_bg_color(black_square, lv_color_hex(0x000000), LV_PART_MAIN);
  lv_obj_add_flag(black_square, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(black_square);
  lv_obj_set_style_border_width(black_square, 0, LV_PART_MAIN);
  lv_obj_set_style_radius(black_square, 0, LV_PART_MAIN);
  lv_obj_align(black_square, LV_ALIGN_CENTER, 0, -102);

  lv_obj_t * home_btn = lv_button_create(info_screen);
  lv_obj_align(home_btn, LV_ALIGN_CENTER, 17, -102);
  lv_obj_add_event_cb(home_btn, event_handler_home_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(home_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(home_btn, lv_color_hex(0xffffff), LV_PART_MAIN);
  lv_obj_add_flag(home_btn, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(home_btn);
  //lv_obj_set_style_pad_all(home_btn, 7, LV_PART_MAIN);
  lv_obj_t * home_btn_label = lv_label_create(home_btn);
  lv_label_set_text(home_btn_label, LV_SYMBOL_HOME);
  lv_obj_set_style_text_color(home_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(home_btn_label);

  lv_obj_t * data_btn = lv_button_create(info_screen);
  lv_obj_align(data_btn, LV_ALIGN_CENTER, 71, -102);
  lv_obj_add_event_cb(data_btn, event_handler_data_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(data_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(data_btn, lv_color_hex(0xffffff), LV_PART_MAIN);
  lv_obj_add_flag(data_btn, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(data_btn);
  //lv_obj_set_style_pad_all(data_btn, 7, LV_PART_MAIN);
  lv_obj_t * data_btn_label = lv_label_create(data_btn);
  lv_label_set_text(data_btn_label, "Data");
  lv_obj_set_style_text_color(data_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(data_btn_label);

  lv_obj_t * refresh_btn = lv_button_create(info_screen);
  lv_obj_align(refresh_btn, LV_ALIGN_CENTER, 130, -102);
  lv_obj_add_event_cb(refresh_btn, event_handler_refresh_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(refresh_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(refresh_btn, lv_color_hex(0x393939), LV_PART_MAIN);
  lv_obj_add_flag(refresh_btn, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(refresh_btn);
  //lv_obj_set_style_pad_all(refresh_btn, 7, LV_PART_MAIN);
  lv_obj_t * refresh_btn_label = lv_label_create(refresh_btn);
  lv_label_set_text(refresh_btn_label, "Info");
  lv_obj_set_style_text_color(refresh_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(refresh_btn_label);

  lv_obj_t * blimas_label = lv_label_create(info_screen);
  lv_label_set_text(blimas_label, "BLIMAS");
  lv_obj_set_style_text_font(blimas_label, &lv_font_montserrat_20, LV_PART_MAIN);
  lv_obj_set_style_text_color(blimas_label, lv_color_white(), LV_PART_MAIN);
  lv_obj_align(blimas_label, LV_ALIGN_TOP_LEFT, 25, 7);
  lv_obj_add_flag(blimas_label, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(blimas_label);  

}

void create_data_screen() {
  data_screen = lv_obj_create(NULL);
  //lv_obj_set_scroll_dir(data_screen, LV_DIR_VER);

  data_content_container = lv_obj_create(data_screen);
  lv_obj_set_size(data_content_container, 310, 200);
  lv_obj_align(data_content_container, LV_ALIGN_CENTER, 0, 19);
  lv_obj_set_style_bg_opa(data_content_container, LV_OPA_TRANSP, LV_PART_MAIN);
  lv_obj_set_style_border_width(data_content_container, 0, LV_PART_MAIN);
  lv_obj_set_scroll_dir(data_content_container, LV_DIR_VER);

  lv_obj_t * title = lv_label_create(data_content_container);
  lv_label_set_text(title, "Daily statistics");
  lv_obj_set_style_text_font(title, &lv_font_montserrat_20, LV_PART_MAIN);
  lv_obj_set_style_text_color(title, lv_color_hex(0x2196f3), LV_PART_MAIN);
  lv_obj_align(title, LV_ALIGN_TOP_MID, 0, 0);

  lv_obj_t * date_label = lv_label_create(data_content_container);
  lv_label_set_text(date_label, "Today's Summary");
  lv_obj_set_style_text_color(date_label, lv_color_hex(0x666666), LV_PART_MAIN);
  lv_obj_align(date_label, LV_ALIGN_TOP_MID, 0, 30);

  int y_pos = 60;

  create_data_card(data_content_container, "Air Temperature", "°C", 0, y_pos, 0x2196f3);
  y_pos += 120;
  create_data_card(data_content_container, "Humidity", "%", 1, y_pos, 0x4caf50);
  y_pos += 120;
  create_data_card(data_content_container, "Water Level", "cm", 2, y_pos, 0x00bcd4);
  y_pos += 120;
  create_data_card(data_content_container, "Surface Temp", "°C", 3, y_pos, 0xff9800);
  y_pos += 120;
  create_data_card(data_content_container, "Mid Temp", "°C", 4, y_pos, 0xff5722);
  y_pos += 120;
  create_data_card(data_content_container, "Deep Temp", "°C", 5, y_pos, 0x9c27b0);

  lv_obj_t * black_square = lv_obj_create(data_screen);
  lv_obj_set_size(black_square, 320, 36);
  lv_obj_set_style_bg_color(black_square, lv_color_hex(0x000000), LV_PART_MAIN);
  lv_obj_add_flag(black_square, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(black_square);
  lv_obj_set_style_border_width(black_square, 0, LV_PART_MAIN);
  lv_obj_set_style_radius(black_square, 0, LV_PART_MAIN);
  lv_obj_align(black_square, LV_ALIGN_CENTER, 0, -102);

  lv_obj_t * home_btn = lv_button_create(data_screen);
  lv_obj_align(home_btn, LV_ALIGN_CENTER, 17, -102);
  lv_obj_add_event_cb(home_btn, event_handler_home_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(home_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(home_btn, lv_color_hex(0xffffff), LV_PART_MAIN);
  lv_obj_add_flag(home_btn, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(home_btn);
  //lv_obj_set_style_pad_all(home_btn, 7, LV_PART_MAIN);
  lv_obj_t * home_btn_label = lv_label_create(home_btn);
  lv_label_set_text(home_btn_label, LV_SYMBOL_HOME);
  lv_obj_set_style_text_color(home_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(home_btn_label);

  lv_obj_t * data_btn = lv_button_create(data_screen);
  lv_obj_align(data_btn, LV_ALIGN_CENTER, 71, -102);
  lv_obj_add_event_cb(data_btn, event_handler_data_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(data_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(data_btn, lv_color_hex(0x393939), LV_PART_MAIN);
  lv_obj_add_flag(data_btn, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(data_btn);
  //lv_obj_set_style_pad_all(data_btn, 7, LV_PART_MAIN);
  lv_obj_t * data_btn_label = lv_label_create(data_btn);
  lv_label_set_text(data_btn_label, "Data");
  lv_obj_set_style_text_color(data_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(data_btn_label);

  lv_obj_t * refresh_btn = lv_button_create(data_screen);
  lv_obj_align(refresh_btn, LV_ALIGN_CENTER, 130, -102);
  lv_obj_add_event_cb(refresh_btn, event_handler_refresh_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(refresh_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(refresh_btn, lv_color_hex(0xffffff), LV_PART_MAIN);
  lv_obj_add_flag(refresh_btn, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(refresh_btn);
  //lv_obj_set_style_pad_all(refresh_btn, 7, LV_PART_MAIN);
  lv_obj_t * refresh_btn_label = lv_label_create(refresh_btn);
  lv_label_set_text(refresh_btn_label, "Info");
  lv_obj_set_style_text_color(refresh_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(refresh_btn_label);

  lv_obj_t * blimas_label = lv_label_create(data_screen);
  lv_label_set_text(blimas_label, "BLIMAS");
  lv_obj_set_style_text_font(blimas_label, &lv_font_montserrat_20, LV_PART_MAIN);
  lv_obj_set_style_text_color(blimas_label, lv_color_white(), LV_PART_MAIN);
  lv_obj_align(blimas_label, LV_ALIGN_TOP_LEFT, 25, 7);
  lv_obj_add_flag(blimas_label, LV_OBJ_FLAG_FLOATING);
  lv_obj_move_foreground(blimas_label);  

}

void lv_create_main_gui() {
  main_screen = lv_obj_create(NULL);
  lv_obj_set_style_bg_color(main_screen, lv_color_white(), LV_PART_MAIN);
  lv_scr_load(main_screen);

  lv_obj_t * black_square = lv_obj_create(main_screen);
  lv_obj_set_size(black_square, 320, 36);
  lv_obj_set_style_bg_color(black_square, lv_color_hex(0x000000), LV_PART_MAIN);
  lv_obj_set_style_border_width(black_square, 0, LV_PART_MAIN);
  lv_obj_set_style_radius(black_square, 0, LV_PART_MAIN);
  lv_obj_align(black_square, LV_ALIGN_CENTER, 0, -102);

  lv_obj_t * black_square2 = lv_obj_create(main_screen);
  lv_obj_set_size(black_square2, 320, 18);
  lv_obj_set_style_bg_color(black_square2, lv_color_hex(0xd9d9d9), LV_PART_MAIN);
  lv_obj_set_style_border_width(black_square2, 0, LV_PART_MAIN);
  lv_obj_set_style_radius(black_square2, 0, LV_PART_MAIN);
  lv_obj_align(black_square2, LV_ALIGN_CENTER, 0, 111);

  lv_obj_t * home_btn = lv_button_create(main_screen);
  lv_obj_align(home_btn, LV_ALIGN_CENTER, 17, -102); //4
  lv_obj_add_event_cb(home_btn, event_handler_home_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(home_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(home_btn, lv_color_hex(0x393939), LV_PART_MAIN);
  //lv_obj_set_style_pad_all(home_btn, 7, LV_PART_MAIN);
  lv_obj_t * home_btn_label = lv_label_create(home_btn);
  lv_label_set_text(home_btn_label, LV_SYMBOL_HOME);
  lv_obj_set_style_text_color(home_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(home_btn_label);

  lv_obj_t * data_btn = lv_button_create(main_screen);
  lv_obj_align(data_btn, LV_ALIGN_CENTER, 71, -102);
  lv_obj_add_event_cb(data_btn, event_handler_data_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(data_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(data_btn, lv_color_hex(0xffffff), LV_PART_MAIN);
  //lv_obj_set_style_pad_all(data_btn, 7, LV_PART_MAIN);
  lv_obj_t * data_btn_label = lv_label_create(data_btn);
  lv_label_set_text(data_btn_label, "Data");
  lv_obj_set_style_text_color(data_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(data_btn_label);

  lv_obj_t * refresh_btn = lv_button_create(main_screen);
  lv_obj_align(refresh_btn, LV_ALIGN_CENTER, 130, -102);
  lv_obj_add_event_cb(refresh_btn, event_handler_refresh_btn, LV_EVENT_ALL, NULL);
  lv_obj_set_height(refresh_btn, LV_SIZE_CONTENT);
  lv_obj_set_style_bg_color(refresh_btn, lv_color_hex(0xffffff), LV_PART_MAIN);
  //lv_obj_set_style_pad_all(refresh_btn, 7, LV_PART_MAIN);
  lv_obj_t * refresh_btn_label = lv_label_create(refresh_btn);
  lv_label_set_text(refresh_btn_label, "Info");
  lv_obj_set_style_text_color(refresh_btn_label, lv_color_hex(0xffa200), LV_PART_MAIN);
  lv_obj_center(refresh_btn_label);

  airTempLabel = lv_label_create(main_screen);
  lv_obj_align(airTempLabel, LV_ALIGN_TOP_LEFT, 55, 82);

  waterLevelLabel = lv_label_create(main_screen);
  lv_obj_align(waterLevelLabel, LV_ALIGN_TOP_LEFT, 160, 82);

  humidityLabel = lv_label_create(main_screen);
  lv_obj_align(humidityLabel, LV_ALIGN_TOP_LEFT, 258, 82);

  wt1Label = lv_label_create(main_screen);
  lv_obj_align(wt1Label, LV_ALIGN_TOP_LEFT, 65, 159);
  lv_obj_set_style_text_font(wt1Label, &lv_font_montserrat_12, 0);

  wt2Label = lv_label_create(main_screen);
  lv_obj_align(wt2Label, LV_ALIGN_TOP_LEFT, 65, 172);
  lv_obj_set_style_text_font(wt2Label, &lv_font_montserrat_12, 0);

  wt3Label = lv_label_create(main_screen);
  lv_obj_align(wt3Label, LV_ALIGN_TOP_LEFT, 65, 186);
  lv_obj_set_style_text_font(wt3Label, &lv_font_montserrat_12, 0);

  timeLabel = lv_label_create(main_screen);
  lv_obj_set_style_text_font(timeLabel, &lv_font_montserrat_20, 0);
  lv_obj_set_style_text_color(timeLabel, lv_color_make(0, 0, 0), 0);
  lv_obj_align(timeLabel, LV_ALIGN_TOP_LEFT, 200, 189);

  greetingLabel = lv_label_create(main_screen);
  lv_obj_set_style_text_font(greetingLabel, &lv_font_montserrat_16, 0);
  lv_obj_set_style_text_color(greetingLabel, lv_color_make(0, 0, 0), 0);
  lv_obj_align(greetingLabel, LV_ALIGN_TOP_LEFT, 18, 45);

  drawImageHum(main_screen, 230, 78);
  drawImageWl(main_screen, 129, 78);
  drawImageClk(main_screen, 226, 142);
  drawImageTmp(main_screen, 31, 78);
  drawImageWt(main_screen, 26, 163);
  drawImageBat(main_screen, 22, 226);
  drawImageWifi(main_screen, 5, 224);
  drawImagepwr(main_screen, 303, 224);

  textWrite(main_screen, 25, 7, "BLIMAS", &lv_font_montserrat_20, 255, 255, 255);
  textWrite(main_screen, 239, 105, "Humidity");
  textWrite(main_screen, 135, 105, "Water Level");
  textWrite(main_screen, 30, 105, "Temperature");
  textWrite(main_screen, 18, 134, "Water Temperature", &lv_font_montserrat_16);
  textWrite(main_screen, 107, 223, "Bolgoda Lake - Katubedda", &lv_font_montserrat_14);

}

void setup() {
  Serial.begin(115200);
  Serial.printf("LVGL Version: %d.%d.%d\n", lv_version_major(), lv_version_minor(), lv_version_patch());

  lv_init();
  lv_log_register_print_cb(log_print);

  touchscreenSPI.begin(XPT2046_CLK, XPT2046_MISO, XPT2046_MOSI, XPT2046_CS);
  touchscreen.begin(touchscreenSPI);
  touchscreen.setRotation(2);

  lv_display_t * disp = lv_tft_espi_create(SCREEN_WIDTH, SCREEN_HEIGHT, draw_buf, sizeof(draw_buf));
  lv_display_set_rotation(disp, LV_DISPLAY_ROTATION_270);

  lv_indev_t * indev = lv_indev_create();
  lv_indev_set_type(indev, LV_INDEV_TYPE_POINTER);
  lv_indev_set_read_cb(indev, touchscreen_read);

  // Initialize LED pins
  pinMode(CYD_LED_RED, OUTPUT);
  pinMode(CYD_LED_GREEN, OUTPUT);
  pinMode(CYD_LED_BLUE, OUTPUT);
  
  create_loading_screen();
  update_loading_progress("Initializing...", 10);

  air_avg_label = NULL; air_max_label = NULL; air_min_label = NULL;
  hum_avg_label = NULL; hum_max_label = NULL; hum_min_label = NULL;
  wl_avg_label = NULL; wl_max_label = NULL; wl_min_label = NULL;
  wt1_avg_label = NULL; wt1_max_label = NULL; wt1_min_label = NULL;
  wt2_avg_label = NULL; wt2_max_label = NULL; wt2_min_label = NULL;
  wt3_avg_label = NULL; wt3_max_label = NULL; wt3_min_label = NULL;
  
  update_loading_progress("Creating UI...", 30);
  lv_scr_load(loading_screen);
  lv_create_main_gui();
  create_info_screen();
  lv_scr_load(loading_screen);

  update_loading_progress("Connecting to WiFi...", 50);
  connectToWiFi();
  
  update_loading_progress("Syncing time...", 70);
  setupTime();
  updateTimeAndGreeting(NULL);

  update_loading_progress("Starting services...", 85);
  lv_scr_load(loading_screen);
  lv_timer_create(fetchData, 300000, NULL);
  lv_timer_create(fetch_daily_stats, 300000, NULL);
  setupAutoBrightness();
  lv_timer_create(autoBrightnessTimer, 5000, NULL);
  lv_timer_create(updateTimeAndGreeting, 5*60*1000, NULL);
  lv_scr_load(loading_screen);
  
  update_loading_progress("Complete!", 100);
  lv_scr_load(loading_screen);
  
  lv_scr_load(main_screen);
  
  fetchData(NULL);
  fetch_daily_stats(NULL);
}

void loop() {
  lv_task_handler();
  lv_tick_inc(5);
  delay(5);
}
