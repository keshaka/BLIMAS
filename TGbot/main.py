from telegram import Update, Bot
from telegram.ext import (
    Application,
    CommandHandler,
    ContextTypes,
)
import requests
import schedule
import time

# Telegram Bot Token
BOT_TOKEN = ""
API_ENDPOINT = "http://blimas.pasgorasa.site/get_sensor_data.php"  # Replace with your sensor data endpoint
CHAT_ID = "1066891806"

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
                alerts.append(f"⚠️ Problem detected with {sensor}: value is invalid (not a number).")

    try:
        if "distance" in data and (float(data["distance"]) > 0):
            if "distance" in data and (float(data["distance"]) < 450):
                alerts.append(f"⚠️ Water level alert: {data['distance']} cm (outside safe range).")
            if "distance" in data and (float(data["distance"]) > 470):
                alerts.append(f"⚠️ Water level alert: {data['distance']} cm (outside safe range).")
    except:
        alerts.append(f"⚠️ Problem detected with ultrasonic sensor: value is invalid.")


    if alerts:
        alert_message = "\n".join(alerts)
        await update.message.reply_text(alert_message)
    else:
        await update.message.reply_text("✅ All sensors are operating normally.")
        

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
