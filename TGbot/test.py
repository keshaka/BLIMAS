from telegram import Update, Bot
from telegram.ext import (
    Application,
    CommandHandler,
    ContextTypes,
)
import requests
import schedule
import time
import subprocess
import os

# Telegram Bot Token
BOT_TOKEN = ""
TOKEN = ""
API_ENDPOINT = "http://blimas.pasgorasa.site/get_sensor_data.php"  # Replace with your sensor data endpoint
CHAT_ID = "1066891806"


# Function to dump the RDS database and send it via Telegram
async def download_database(update: Update, context: ContextTypes.DEFAULT_TYPE):
    DB_HOST = "sensor.cxkk2guqqpwa.ap-southeast-1.rds.amazonaws.com"
    DB_USER = "keshaka"
    DB_PASSWORD = "alohomora"
    DB_NAME = "sensor_data"
    DB_PORT = 3306  # Change this to 5432 for PostgreSQL
    dump_file = "database_dump.sql"

    try:
        # Create the database dump using mysqldump or pg_dump
        dump_command = [
            "mysqldump",  # Use "pg_dump" for PostgreSQL
            f"--host={DB_HOST}",
            f"--port={DB_PORT}",
            f"--user={DB_USER}",
            f"--password={DB_PASSWORD}",
            DB_NAME
        ]

        with open(dump_file, "w") as f:
            result = subprocess.run(dump_command, stdout=f, stderr=subprocess.PIPE, text=True)

        if result.returncode == 0:
            print(f"Database dump created successfully: {dump_file}")

            # Send the dump file via Telegram
            await context.bot.send_document(chat_id=CHAT_ID, document=open(dump_file, "rb"))
            print("Database dump sent successfully via Telegram.")
        else:
            print(f"Error during database dump: {result.stderr}")
            await update.message.reply_text("⚠️ Failed to create database dump! Check logs for details.")
    except Exception as e:
        print(f"Error while creating or sending the database dump: {e}")
        await update.message.reply_text("⚠️ An error occurred while processing the database dump.")
    finally:
        # Clean up local dump file if it exists
        if os.path.exists(dump_file):
            os.remove(dump_file)
            print("Temporary dump file removed.")


# Function to fetch sensor data
def fetch_sensor_data():
    try:
        response = requests.get(API_ENDPOINT)
        if response.status_code == 200:
            return response.json()
        else:
            print(f"Error fetching data: {response.status_code}")
            return None
    except Exception as e:
        print(f"Exception while fetching data: {e}")
        return None

# Command: /start
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    welcome_message = (
        "👋 Welcome to the Sensor Monitoring Bot!\n\n"
        "Available commands:\n"
        "📊 /check - Get the latest sensor readings.\n"
        "⚠️ /alerts - Get current alerts.\n"
        "ℹ️ /help - Show help information."
    )
    await update.message.reply_text(welcome_message)

# Command: /check
async def check_sensor_data(update: Update, context: ContextTypes.DEFAULT_TYPE):
    data = fetch_sensor_data()
    if not data:
        await update.message.reply_text("⚠️ Error fetching sensor data!")
        return

    summary = "📊 Current Sensor Readings:\n" + "\n".join(
        [f"{sensor}: {value}" for sensor, value in data.items()]
    )
    await update.message.reply_text(summary)

# Command: /alerts
async def check_alerts(update: Update, context: ContextTypes.DEFAULT_TYPE):
    data = fetch_sensor_data()
    if not data:
        await update.message.reply_text("⚠️ Error fetching sensor data!")
        return

    alerts = []
    for sensor, value in data.items():
        if (sensor != "timestamp"):
            try:
                value = float(value)  # Convert the value to a float
                if value <= 0:
                    alerts.append(f"⚠️ Problem detected with {sensor}: value is {value} (check the sensor).")
            except ValueError:
                alerts.append(f"⚠️ Problem detected with {sensor}: value is invalid (not a number). {sensor} සෙන්සරේ ගහපාන්.")

    try:
        if "distance" in data and (float(data["distance"]) > 0):
            if "distance" in data and (float(data["distance"]) < 30):
                alerts.append(f"⚠️ Water level alert: {data['distance']} cm (outside safe range). ගංවතුර එනවෝ. දුවපල්ලා. 🏃‍♂️🌊")
            if "distance" in data and (float(data["distance"]) > 50):
                alerts.append(f"⚠️ Water level alert: {data['distance']} cm (outside safe range). නියගයක් එනවෝ. 🚱")
    except:
        alerts.append(f"⚠️ Problem detected with ultrasonic sensor: value is invalid. jsn සෙන්සරේ ගහපාන්. ")

    if "tempDHT" in data and (float(data['tempDHT']) > 32):
            alerts.append(f"අම්මෝ..... අමාරුයී....... ෆෑන් එක දාපාන්............................. 🥵")

    if alerts:
        alert_message = "\n".join(alerts)
        await update.message.reply_text(alert_message)
    else:
        await update.message.reply_text("✅ All sensors are operating normally. ඔක්කොම බඩු හොදට වැඩ.")
        

# Command: /help
async def help_command(update: Update, context: ContextTypes.DEFAULT_TYPE):
    help_message = (
        "ℹ️ Help Information:\n\n"
        "Commands:\n"
        "📊 /check - Get the latest sensor readings.\n"
        "⚠️ /alerts - Get current alerts.\n"
        "ℹ️ /help - Show this help message."
    )
    await update.message.reply_text(help_message)

# Daily summary function (manual execution)
async def send_daily_summary():
    data = fetch_sensor_data()
    if not data:
        print("⚠️ Error fetching sensor data for daily summary!")
        return

    summary = "📊 Daily Sensor Summary:\n" + "\n".join(
        [f"{sensor}: {value}" for sensor, value in data.items()]
    )
    bot = Bot(token=BOT_TOKEN)
    await bot.send_message(chat_id="YOUR_CHAT_ID", text=summary)

# Main function to start the bot
def main():
    app = Application.builder().token(BOT_TOKEN).build()

    # Add command handlers
    app.add_handler(CommandHandler("start", start))
    app.add_handler(CommandHandler("check", check_sensor_data))
    app.add_handler(CommandHandler("alerts", check_alerts))
    app.add_handler(CommandHandler("help", help_command))
    app.add_handler(CommandHandler("download", download_database))

    # Start the bot
    print("Bot is running...")
    app.run_polling()

    # Schedule tasks (if needed)
    schedule.every().day.at("09:00").do(lambda: asyncio.run(send_daily_summary()))
    schedule.every(1).minutes.do(check_alerts)  # Alerts every 5 minutes


    while True:
        schedule.run_pending()
        time.sleep(10)

if __name__ == "__main__":
    main()
