import logging
import schedule
import time
import threading
import requests
import mysql.connector
from datetime import datetime, timedelta
from telegram import Update, Bot
from telegram.ext import ApplicationBuilder, CommandHandler, ContextTypes
import asyncio

# -----------------------------
# CONFIGURATION
# -----------------------------

BOT_TOKEN = ''
ADMIN_IDS = []

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'blimas_db'
}

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
            AVG(humidity) AS avg_humidity,
            MIN(water_level) AS min_water_level,
            MAX(water_level) AS max_water_level,
            AVG(water_level) AS avg_water_level,
            MIN(water_temp_depth1) AS min_surface_temp,
            MAX(water_temp_depth1) AS max_surface_temp,
            AVG(water_temp_depth1) AS avg_surface_temp,
            MIN(water_temp_depth2) AS min_mid_temp,
            MAX(water_temp_depth2) AS max_mid_temp,
            AVG(water_temp_depth2) AS avg_mid_temp,
            MIN(water_temp_depth3) AS min_bottom_temp,
            MAX(water_temp_depth3) AS max_bottom_temp,
            AVG(water_temp_depth3) AS avg_bottom_temp
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
        f"💧 Humidity: Min {stats['min_humidity']}% / Max {stats['max_humidity']}% / Avg {round(stats['avg_humidity'],2)}%\n"
        f"🌊 Water Level: Min {stats['min_water_level']} cm / Max {stats['max_water_level']} cm / Avg {round(stats['avg_water_level'],2)} cm\n"
        f"🌡️ Surface Temp: Min {stats['min_surface_temp']}°C / Max {stats['max_surface_temp']}°C / Avg {round(stats['avg_surface_temp'],2)}°C\n"
        f"🌡️ Mid Temp: Min {stats['min_mid_temp']}°C / Max {stats['max_mid_temp']}°C / Avg {round(stats['avg_mid_temp'],2)}°C\n"
        f"🌡️ Bottom Temp: Min {stats['min_bottom_temp']}°C / Max {stats['max_bottom_temp']}°C / Avg {round(stats['avg_bottom_temp'],2)}°C"
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
# AUTOMATIC BACKUP SENDER
# -----------------------------

async def send_backup_to_admins(bot: Bot):
    print("Attempting to send backup to admins...")  # Debug
    filename = f"/tmp/blimas_backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}.sql"
    import subprocess
    subprocess.call(f"mysqldump -u {DB_CONFIG['user']} -p{DB_CONFIG['password']} {DB_CONFIG['database']} > {filename}", shell=True)
    for admin_id in ADMIN_IDS:
        try:
            await bot.send_document(chat_id=admin_id, document=open(filename, 'rb'))
            print(f"Backup sent to {admin_id}")  # Debug
        except Exception as e:
            print(f"Failed to send backup to admin {admin_id}: {e}")
    subprocess.call(f"rm {filename}", shell=True)

# -----------------------------
# MONITORING FUNCTION
# -----------------------------

async def check_warnings(bot: Bot):
    sensor, battery = fetch_latest_data()
    if not sensor or not battery:
        return

    warnings = []

    if sensor['water_level'] > 150.00:
        warnings.append(f"⚠️ Water level high: {sensor['water_level']} cm")

    if battery['battery_percentage'] < 20:
        warnings.append(f"🔋 Low battery: {battery['battery_percentage']}%")

    for field in ['air_temperature', 'humidity', 'water_temp_depth1', 'water_temp_depth2', 'water_temp_depth3']:
        if sensor[field] == 0:
            warnings.append(f"🚨 Sensor failure detected: {field} is 0")

    if warnings:
        text = "🚨 BLIMAS Alert:\n" + "\n".join(warnings)
        for admin_id in ADMIN_IDS:
            await bot.send_message(chat_id=admin_id, text=text)

# -----------------------------
# CHECK NEW DATA FUNCTION
# -----------------------------

last_known_timestamp = None

async def check_new_data(bot: Bot):
    global last_known_timestamp
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    cursor.execute("SELECT timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 1")
    result = cursor.fetchone()
    conn.close()
    if result:
        latest_timestamp = result[0]
        if last_known_timestamp is None:
            last_known_timestamp = latest_timestamp
        elif latest_timestamp == last_known_timestamp:
            # No new data added, send warning
            for admin_id in ADMIN_IDS:
                await bot.send_message(chat_id=admin_id, text=f"⚠️ No new sensor data detected since {latest_timestamp}. Please check the system!")
        else:
            # New data was added, update last_known_timestamp, do nothing
            last_known_timestamp = latest_timestamp
    else:
        print("No data in sensor_data table.")
        for admin_id in ADMIN_IDS:
            await bot.send_message(chat_id=admin_id, text="🚨 Missing Latest sensor data. Please check the database connection or sensor data insertion process.")

# -----------------------------
# CHECK WATER LEVEL CHANGE FUNCTION
# -----------------------------

async def check_water_level_change(bot: Bot):
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT water_level, timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 2")
    results = cursor.fetchall()
    conn.close()
    if len(results) == 2:
        latest = results[0]
        previous = results[1]
        diff = abs(latest['water_level'] - previous['water_level'])
        if diff > 10:
            for admin_id in ADMIN_IDS:
                await bot.send_message(
                    chat_id=admin_id,
                    text=f"⚠️ Sudden water level change detected!\nPrevious: {previous['water_level']} cm at {previous['timestamp']}\nLatest: {latest['water_level']} cm at {latest['timestamp']}\nDifference: {diff} cm"
                )

# -----------------------------
# BACKGROUND JOBS (Method One: run_coroutine_threadsafe)
# -----------------------------

main_loop = asyncio.get_event_loop()

def run_schedule(bot: Bot):
    async def warning_job():
        await check_warnings(bot)
    async def backup_job():
        await send_backup_to_admins(bot)
    async def new_data_job():
        await check_new_data(bot)
    async def water_level_change_job():
        await check_water_level_change(bot)

    schedule.every(5).minutes.do(lambda: asyncio.run_coroutine_threadsafe(warning_job(), main_loop))
    schedule.every(1440).minutes.do(lambda: asyncio.run_coroutine_threadsafe(backup_job(), main_loop))
    schedule.every(6).minutes.do(lambda: asyncio.run_coroutine_threadsafe(new_data_job(), main_loop))
    schedule.every(6).minutes.do(lambda: asyncio.run_coroutine_threadsafe(water_level_change_job(), main_loop))

    while True:
        schedule.run_pending()
        time.sleep(1)

# -----------------------------
# MAIN FUNCTION
# -----------------------------

def main():
    app = ApplicationBuilder().token(BOT_TOKEN).build()

    app.add_handler(CommandHandler("start", start))
    app.add_handler(CommandHandler("status", status))
    app.add_handler(CommandHandler("summary", summary))
    app.add_handler(CommandHandler("backup", backup))

    # Start background monitoring thread
    threading.Thread(target=run_schedule, args=(app.bot,), daemon=True).start()

    app.run_polling()

if __name__ == '__main__':
    main()