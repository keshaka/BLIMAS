/*  Rui Santos & Sara Santos - Random Nerd Tutorials - https://RandomNerdTutorials.com/esp32-cyd-lvgl-display-image/
    THIS EXAMPLE WAS TESTED WITH THE FOLLOWING HARDWARE:
    1) ESP32-2432S028R 2.8 inch 240×320 also known as the Cheap Yellow Display (CYD): https://makeradvisor.com/tools/cyd-cheap-yellow-display-esp32-2432s028r/
      SET UP INSTRUCTIONS: https://RandomNerdTutorials.com/cyd-lvgl/
    2) REGULAR ESP32 Dev Board + 2.8 inch 240x320 TFT Display: https://makeradvisor.com/tools/2-8-inch-ili9341-tft-240x320/ and https://makeradvisor.com/tools/esp32-dev-board-wi-fi-bluetooth/
      SET UP INSTRUCTIONS: https://RandomNerdTutorials.com/esp32-tft-lvgl/
    Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files.
    The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*/

#ifdef __has_include
    #if __has_include("lvgl.h")
        #ifndef LV_LVGL_H_INCLUDE_SIMPLE
            #define LV_LVGL_H_INCLUDE_SIMPLE
        #endif
    #endif
#endif

#if defined(LV_LVGL_H_INCLUDE_SIMPLE)
    #include "lvgl.h"
#else
    #include "lvgl/lvgl.h"
#endif

#define LV_BIG_ENDIAN_SYSTEM

#ifndef LV_ATTRIBUTE_MEM_ALIGN
#define LV_ATTRIBUTE_MEM_ALIGN
#endif

#ifndef LV_ATTRIBUTE_IMG_MY_IMAGE
#define LV_ATTRIBUTE_IMG_MY_IMAGE
#endif

const LV_ATTRIBUTE_MEM_ALIGN LV_ATTRIBUTE_IMG_MY_IMAGE uint8_t pwr_map[] = {
    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x0d, 0x0d, 0x0d, 0x4d, 0x03, 0x03, 0x03, 0xeb, 0x16, 0x16, 0x16, 0x23, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 
    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x0e, 0x0e, 0x0e, 0x70, 0x00, 0x00, 0x00, 0xff, 0x15, 0x15, 0x15, 0x3c, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 
    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x0e, 0x0e, 0x0e, 0x36, 0x08, 0x08, 0x08, 0x7c, 0x40, 0x40, 0x40, 0x04, 0x0e, 0x0e, 0x0e, 0x70, 0x00, 0x00, 0x00, 0xff, 0x15, 0x15, 0x15, 0x3c, 0x17, 0x17, 0x17, 0x16, 0x08, 0x08, 0x08, 0x86, 0x10, 0x10, 0x10, 0x1f, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 
    0x00, 0x00, 0x00, 0x00, 0x0d, 0x0d, 0x0d, 0x4e, 0x01, 0x01, 0x01, 0xee, 0x02, 0x02, 0x02, 0xea, 0x2a, 0x2a, 0x2a, 0x0c, 0x0e, 0x0e, 0x0e, 0x70, 0x00, 0x00, 0x00, 0xff, 0x15, 0x15, 0x15, 0x3c, 0x12, 0x12, 0x12, 0x38, 0x01, 0x01, 0x01, 0xf7, 0x02, 0x02, 0x02, 0xdb, 0x0e, 0x0e, 0x0e, 0x25, 0x00, 0x00, 0x00, 0x00, 
    0x0f, 0x0f, 0x0f, 0x21, 0x01, 0x01, 0x01, 0xef, 0x02, 0x02, 0x02, 0xe1, 0x0f, 0x0f, 0x0f, 0x34, 0x00, 0x00, 0x00, 0x00, 0x0e, 0x0e, 0x0e, 0x70, 0x00, 0x00, 0x00, 0xff, 0x15, 0x15, 0x15, 0x3c, 0x00, 0x00, 0x00, 0x02, 0x09, 0x09, 0x09, 0x54, 0x01, 0x01, 0x01, 0xf2, 0x03, 0x03, 0x03, 0xc9, 0x11, 0x11, 0x11, 0x0f, 
    0x06, 0x06, 0x06, 0x80, 0x01, 0x01, 0x01, 0xf6, 0x0b, 0x0b, 0x0b, 0x44, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x0e, 0x0e, 0x0e, 0x70, 0x00, 0x00, 0x00, 0xff, 0x15, 0x15, 0x15, 0x3c, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x06, 0x06, 0x06, 0x76, 0x01, 0x01, 0x01, 0xf9, 0x0a, 0x0a, 0x0a, 0x4e, 
    0x05, 0x05, 0x05, 0xc7, 0x01, 0x01, 0x01, 0xd4, 0x11, 0x11, 0x11, 0x0f, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x0d, 0x0d, 0x0d, 0x62, 0x00, 0x00, 0x00, 0xff, 0x15, 0x15, 0x15, 0x30, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x0d, 0x0d, 0x0d, 0x28, 0x01, 0x01, 0x01, 0xf2, 0x06, 0x06, 0x06, 0x9d, 
    0x03, 0x03, 0x03, 0xe7, 0x01, 0x01, 0x01, 0xbf, 0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x33, 0x33, 0x33, 0x05, 0x16, 0x16, 0x16, 0x2f, 0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x27, 0x27, 0x27, 0x0d, 0x00, 0x00, 0x00, 0xec, 0x07, 0x07, 0x07, 0xc3, 
    0x04, 0x04, 0x04, 0xda, 0x01, 0x01, 0x01, 0xc8, 0x33, 0x33, 0x33, 0x05, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x15, 0x15, 0x15, 0x18, 0x01, 0x01, 0x01, 0xee, 0x07, 0x07, 0x07, 0xb6, 
    0x06, 0x06, 0x06, 0xa3, 0x01, 0x01, 0x01, 0xe6, 0x0f, 0x0f, 0x0f, 0x23, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x07, 0x07, 0x07, 0x49, 0x01, 0x01, 0x01, 0xf8, 0x09, 0x09, 0x09, 0x73, 
    0x0a, 0x0a, 0x0a, 0x4b, 0x00, 0x00, 0x00, 0xfa, 0x06, 0x06, 0x06, 0x9d, 0x33, 0x33, 0x33, 0x05, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x12, 0x12, 0x12, 0x0e, 0x03, 0x03, 0x03, 0xc8, 0x01, 0x01, 0x01, 0xea, 0x0d, 0x0d, 0x0d, 0x28, 
    0x55, 0x55, 0x55, 0x03, 0x06, 0x06, 0x06, 0xa6, 0x00, 0x00, 0x00, 0xfb, 0x05, 0x05, 0x05, 0xa1, 0x0e, 0x0e, 0x0e, 0x25, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00, 0x00, 0x00, 0x2a, 0x2a, 0x2a, 0x06, 0x09, 0x09, 0x09, 0x39, 0x03, 0x03, 0x03, 0xc4, 0x01, 0x01, 0x01, 0xf9, 0x09, 0x09, 0x09, 0x72, 0x00, 0x00, 0x00, 0x00, 
    0x00, 0x00, 0x00, 0x00, 0x12, 0x12, 0x12, 0x0e, 0x03, 0x03, 0x03, 0x9d, 0x01, 0x01, 0x01, 0xf4, 0x01, 0x01, 0x01, 0xef, 0x02, 0x02, 0x02, 0xd2, 0x05, 0x05, 0x05, 0xbb, 0x03, 0x03, 0x03, 0xdc, 0x01, 0x01, 0x01, 0xf4, 0x01, 0x01, 0x01, 0xef, 0x06, 0x06, 0x06, 0x78, 0x00, 0x00, 0x00, 0x05, 0x00, 0x00, 0x00, 0x00, 
    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x40, 0x40, 0x40, 0x04, 0x0c, 0x0c, 0x0c, 0x40, 0x06, 0x06, 0x06, 0xa6, 0x04, 0x04, 0x04, 0xe9, 0x03, 0x03, 0x03, 0xfc, 0x03, 0x03, 0x03, 0xdf, 0x07, 0x07, 0x07, 0x90, 0x0a, 0x0a, 0x0a, 0x32, 0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 
  };

const lv_image_dsc_t pwr = {
    .header = {
        .magic = LV_IMAGE_HEADER_MAGIC,
        .cf = LV_COLOR_FORMAT_ARGB8888,
        .flags = 0,          
        .w = 13,
        .h = 14,
        //.stride = 120,
        .reserved_2 = 0
    },
    .data_size = sizeof(pwr_map),
    .data = pwr_map,
    .reserved = NULL
};