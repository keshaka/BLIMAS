import time
from telegram.ext import Application, ContextTypes
from telegram.error import TelegramError
from telegram import Update, Bot
from telegram.ext import (
    Application,
    CommandHandler,
    ContextTypes,
)
import requests
import time

from telegram import Update, Bot
from telegram.ext import (
    Application,
    CommandHandler,
    ContextTypes,
)
import requests
import schedule
import time

# Replace with your actual values
TOKEN = '5602100686:AAHHQIMJB6kmmEP2AMC21MGPelC8tI6fjBY'
CHAT_ID = '1066891806'
API_ENDPOINT = "http://blimas.pasgorasa.site/get_sensor_data.php"  # Replace with your sensor data endpoint


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

# Define the function to send the message
async def send_hi(context: ContextTypes.DEFAULT_TYPE):
    data = fetch_sensor_data()
    if not data:
        await context.bot.send_message(chat_id=CHAT_ID, text="⚠️ Error fetching sensor data!")
        return

    alerts = []
    for sensor, value in data.items():
        if (sensor != "timestamp"):
            try:
                value = float(value)  # Convert the value to a float
                if value <= 0:
                    try:
                        await context.bot.send_message(chat_id=CHAT_ID, text=f"⚠️ Problem detected with {sensor}: value is {value} (check the sensor).")
                        print("Message sent successfully.")
                    except TelegramError as e:
                        print(f"Error sending message: {e}")
            except ValueError:
                try:
                    await context.bot.send_message(chat_id=CHAT_ID, text=f"⚠️ Problem detected with {sensor}: value is invalid (not a number). {sensor} සෙන්සරේ ගහපාන්.")
                    print("Message sent successfully.")
                except TelegramError as e:
                    print(f"Error sending message: {e}")

    try:
        if "distance" in data and (float(data["distance"]) > 0):
            if "distance" in data and (float(data["distance"]) < 30):
                try:
                    await context.bot.send_message(chat_id=CHAT_ID, text=f"⚠️ Water level alert: {data['distance']} cm (outside safe range). ගංවතුර එනවෝ. දුවපල්ලා. 🏃‍♂️🌊")
                    print("Message sent successfully.")
                except TelegramError as e:
                    print(f"Error sending message: {e}")
            if "distance" in data and (float(data["distance"]) > 50):
                try:
                    await context.bot.send_message(chat_id=CHAT_ID, text=f"⚠️ Water level alert: {data['distance']} cm (outside safe range). නියගයක් එනවෝ. 🚱")
                    print("Message sent successfully.")
                except TelegramError as e:
                    print(f"Error sending message: {e}")
    except:
        alerts.append(f"⚠️ Problem detected with ultrasonic sensor: value is invalid. jsn සෙන්සරේ ගහපාන්. ")

    if "tempDHT" in data and (float(data['tempDHT']) > 32):
            try:
                await context.bot.send_message(chat_id=CHAT_ID, text=f"අම්මෝ..... අමාරුයී....... ෆෑන් එක දාපාන්............................. 🥵")
                print("Message sent successfully.")
            except TelegramError as e:
                print(f"Error sending message: {e}")


# Create the Application and get the job queue
application = Application.builder().token(TOKEN).build()

# Schedule the message to send every 10 seconds
application.job_queue.run_repeating(send_hi, interval=30, first=0)

# Start polling for updates
application.run_polling()
