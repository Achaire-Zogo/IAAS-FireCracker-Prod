import psutil
import mysql.connector
import time
import os
import requests
import json

from dotenv import load_dotenv

load_dotenv()

USER_ID = os.getenv('USER_ID')
VM_ID = os.getenv('VM_ID')
URL = os.getenv('URL')
# Database connection setup
# def connect_to_database():
#     return mysql.connector.connect(
#         host="192.168.1.186",       # Replace with your database host
#         user="root",  # Replace with your MySQL username
#         password="root", # Replace with your MySQL password
#         database="firecracker"  # Replace with your database name
#     )

# # Create a table for storing metrics (if not exists)
# def setup_database():
#     connection = connect_to_database()
#     cursor = connection.cursor()
#     cursor.execute('''
#         CREATE TABLE IF NOT EXISTS server_metrics (
#             id INT AUTO_INCREMENT PRIMARY KEY,
#             user_id INT ,
#             timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
#             cpu_usage_percent FLOAT,
#             memory_usage_mbi FLOAT,
#             disk_usage_bytes FLOAT,
#             status VARCHAR(255) DEFAULT 'running'
#         )
#     ''')
#     connection.commit()
#     cursor.close()
#     connection.close()

# Insert metrics into the database
def save_metrics_to_database(url,user_id, vm_id, cpu_usage, memory_usage, disk_usage):
    metrics_data = {
        'user_id': user_id,
        'vm_id': vm_id,
        'cpu_usage': cpu_usage,
        'memory_usage': memory_usage,
        'disk_usage': disk_usage
    }
    headers = {'Content-Type': 'application/json'}

    response = requests.post(url, data=json.dumps(metrics_data), headers=headers)
    #print(response.text)
    
    # connection = connect_to_database()
    # cursor = connection.cursor()
    # # Correct UPDATE query
    # cursor.execute('''
    #     UPDATE virtual_machines
    #     SET cpu_usage_percent = %s, memory_usage_mbi = %s, disk_usage_bytes = %s
    #     WHERE user_id = %s AND status = 'running'
    # ''', (cpu_usage, memory_usage, disk_usage, user_id))  # Pass parameters in correct order
    # connection.commit()
    # cursor.close()
    # connection.close()


# Main function to collect metrics
def collect_and_store_metrics():
    #setup_database()
    while True:
        # Collect CPU usage
        cpu_usage = psutil.cpu_percent(interval=1)

        # Collect memory usage
        memory_info = psutil.virtual_memory()
        memory_usage = memory_info.percent

        # Collect disk usage
        disk_info = psutil.disk_usage('/')
        disk_usage = disk_info.percent

        # Save metrics to the database
        save_metrics_to_database(URL,USER_ID,VM_ID,cpu_usage, memory_usage, disk_usage)

        # Print metrics for debugging
        #print(f"CPU: {cpu_usage}%, Memory: {memory_usage}%, Disk: {disk_usage}%")

        # Delay before the next measurement (e.g., 5 seconds)
        time.sleep(5)

if __name__ == "__main__":
    collect_and_store_metrics()
