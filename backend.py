from flask import Flask, request, render_template
import mysql.connector
import socket
import os

app = Flask(__name__, template_folder='templates')

db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="ironshield"
)
cursor = db.cursor()

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/report', methods=['POST'])
def report_ip():
    data = request.json
    cursor.execute("""
        INSERT INTO logs (ip_origen, ip_destino, puerto_origen, puerto_destino, protocolo, tamano, fecha)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
    """, (
        data.get('ip_origen'),
        data.get('ip_destino'),
        data.get('puerto_origen'),
        data.get('puerto_destino'),
        data.get('protocolo'),
        data.get('tamano'),
        data.get('fecha')
    ))
    db.commit()
    return "OK"

@app.route('/block', methods=['POST'])
def block_ip():
    target = request.json['ip']
    try:
        ip = socket.gethostbyname(target)
    except:
        ip = target
    os.system(f"iptables -A INPUT -s {ip} -j DROP")
    return f"Blocked {ip}"

@app.route('/allow', methods=['POST'])
def allow_ip():
    target = request.json['ip']
    try:
        ip = socket.gethostbyname(target)
    except:
        ip = target
    os.system(f"iptables -A INPUT -s {ip} -j ACCEPT")
    return f"Allowed {ip}"

if __name__ == '__main__':
    app.run(debug=True)
