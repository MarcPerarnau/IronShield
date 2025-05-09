from scapy.all import sniff, IP, TCP, UDP, ICMP
import requests
from datetime import datetime

def packet_callback(packet):
    data = {}

    if packet.haslayer(IP):
        ip_layer = packet[IP]
        data['ip_origen'] = ip_layer.src
        data['ip_destino'] = ip_layer.dst
        data['tamano'] = len(packet)
        data['protocolo'] = ip_layer.proto
        data['fecha'] = datetime.now().isoformat()

        if packet.haslayer(TCP):
            data['puerto_origen'] = packet[TCP].sport
            data['puerto_destino'] = packet[TCP].dport
            data['protocolo'] = 'TCP'
        elif packet.haslayer(UDP):
            data['puerto_origen'] = packet[UDP].sport
            data['puerto_destino'] = packet[UDP].dport
            data['protocolo'] = 'UDP'
        elif packet.haslayer(ICMP):
            data['protocolo'] = 'ICMP'

        print(data)
        requests.post("http://localhost:5000/report", json=data)

sniff(prn=packet_callback, store=0)
