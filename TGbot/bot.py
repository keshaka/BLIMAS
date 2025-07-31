import logging
import schedule
import time
import threading
import requests
import mysql.connector
from datetime import datetime, timedelta
from telegram import Update, Bot
from telegram.ext import ApplicationBuilder, CommandHandler, ContextTypes

# -----------------------------
# CONFIGURATION
# -----------------------------

BOT_TOKEN = '8162818770:AAFJ8g68zhVyId8hdn1AXpu4FbY-IC0ARI8'
ADMIN_IDS = [1066891806]  # Replace with your Telegram user ID(s)

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'blimas_db'
}

WATER_LEVEL_THRESHOLD = 50.00  # Set your water level danger threshold
LOW_BATTERY_THRESHOLD = 20     # Low battery %

# -----------------------------
# LOGGING
# -----------------------------

logging.basicConfig(format='%(asctime)s - %(name)s - %(levelname)s - %(message)s', level=logging.INFO)

# -----------------------------
# DATABASE HELPERS
# -----------------------------

def fetch_latest_data():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1")
    sensor = cursor.fetchone()

    cursor.execute("SELECT * FROM battery_status ORDER BY timestamp DESC LIMIT 1")
    battery = cursor.fetchone()

    conn.close()
    return sensor, battery

def fetch_daily_stats():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    today = datetime.now().strftime('%Y-%m-%d')

    cursor.execute(f"""
        SELECT
            MIN(air_temperature) AS min_temp,
            MAX(air_temperature) AS max_temp,
            AVG(air_temperature) AS avg_temp,
            MIN(humidity) AS min_humidity,
            MAX(humidity) AS max_humidity,
            AVG(humidity) AS avg_humidity
        FROM sensor_data
        WHERE DATE(timestamp) = '{today}'
    """)
    stats = cursor.fetchone()
    conn.close()
    return stats

# -----------------------------
# COMMAND HANDLERS
# -----------------------------

async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text("⛅️ Welcome to BLIMAS Bot! \n" \
    " \n"
    "👉 /status for latest data \n"
    "👉 /summary for daily stats \n"
    "👉 /backup for database backup.")

async def status(update: Update, context: ContextTypes.DEFAULT_TYPE):
    sensor, battery = fetch_latest_data()
    if not sensor or not battery:
        await update.message.reply_text("No data available yet.")
        return

    msg = (
        f"📊 Latest Sensor Data ({sensor['timestamp']}):\n"
        f"🌡️ Air Temp: {sensor['air_temperature']} °C\n"
        f"💧 Humidity: {sensor['humidity']} %\n"
        f"🌊 Water Level: {sensor['water_level']} cm\n"
        f"🌡️ Water Temp D1: {sensor['water_temp_depth1']} °C\n"
        f"🌡️ Water Temp D2: {sensor['water_temp_depth2']} °C\n"
        f"🌡️ Water Temp D3: {sensor['water_temp_depth3']} °C\n\n"
        f"🔋 Battery: {battery['battery_percentage']}% {'⚡ Charging' if battery['is_charging'] else '🔋 Not charging'}\n"
        f"📶 RSSI: {battery['rssi']} dBm"
    )
    await update.message.reply_text(msg)

async def summary(update: Update, context: ContextTypes.DEFAULT_TYPE):
    stats = fetch_daily_stats()
    if not stats or stats['avg_temp'] is None:
        await update.message.reply_text("No daily data available yet.")
        return

    msg = (
        f"📈 Daily Summary (Today):\n"
        f"🌡️ Air Temp: Min {stats['min_temp']}°C / Max {stats['max_temp']}°C / Avg {round(stats['avg_temp'],2)}°C\n"
        f"💧 Humidity: Min {stats['min_humidity']}% / Max {stats['max_humidity']}% / Avg {round(stats['avg_humidity'],2)}%"
    )
    await update.message.reply_text(msg)

async def backup(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    if user_id not in ADMIN_IDS:
        await update.message.reply_text("🚫 You are not authorized to perform this action.")
        return

    filename = f"/tmp/blimas_backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}.sql"
    import subprocess
    subprocess.call(f"mysqldump -u {DB_CONFIG['user']} -p{DB_CONFIG['password']} {DB_CONFIG['database']} > {filename}", shell=True)
    
    await update.message.reply_document(document=open(filename, 'rb'))
    subprocess.call(f"rm {filename}", shell=True)

# -----------------------------
# MONITORING FUNCTION
# -----------------------------

async def check_warnings(bot: Bot):
    sensor, battery = fetch_latest_data()
    if not sensor or not battery:
        return

    warnings = []

    if sensor['water_level'] < WATER_LEVEL_THRESHOLD:
        warnings.append(f"⚠️ Water level low: {sensor['water_level']} cm")

    if battery['battery_percentage'] < LOW_BATTERY_THRESHOLD:
        warnings.append(f"🔋 Low battery: {battery['battery_percentage']}%")

    for field in ['air_temperature', 'humidity', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3']:
        if sensor[field] is None:
            warnings.append(f"🚨 Sensor failure detected: {field} is NULL")

    if warnings:
        text = "🚨 BLIMAS Alert:\n" + "\n".join(warnings)
        for admin_id in ADMIN_IDS:
            await bot.send_message(chat_id=admin_id, text=text)

# -----------------------------
# BACKGROUND JOBS
# -----------------------------

def run_schedule(bot: Bot):
    async def job():
        await check_warnings(bot)

    import asyncio
    schedule.every(5).minutes.do(lambda: asyncio.create_task(job()))

    while True:
        schedule.run_pending()
        time.sleep(1)

# -----------------------------
# MAIN FUNCTION
# -----------------------------

import asyncio

from telegram.ext import ApplicationBuilder

def main():
    app = ApplicationBuilder().token(BOT_TOKEN).build()

    app.add_handler(CommandHandler("start", start))
    app.add_handler(CommandHandler("status", status))
    app.add_handler(CommandHandler("summary", summary))
    app.add_handler(CommandHandler("backup", backup))

    # Start background monitoring thread
    threading.Thread(target=run_schedule, args=(app.bot,), daemon=True).start()

    # This will run the bot in polling mode and block the thread
    app.run_polling()

if __name__ == '__main__':
    main()
