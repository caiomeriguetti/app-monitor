import socket
import sys
from threading import Thread
import requests
import json

# Create a TCP/IP socket
sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)

# Bind the socket to the port
server_address = ('0.0.0.0', 10000)
print >>sys.stderr, 'starting up on %s port %s' % server_address
sock.bind(server_address)

def sendToElastic(jsonStr):
    data = json.loads(jsonStr)
    response = requests.post('http://localhost:9200/%s/log/' % (data['type'],), data=jsonStr)

while True:
    print >>sys.stderr, '\nwaiting to receive message'
    jsonStr, address = sock.recvfrom(1024)
    Thread(target=sendToElastic, args=(jsonStr,)).start()
    print >>sys.stderr, 'received %s bytes from %s' % (len(jsonStr), address)
    print >>sys.stderr, jsonStr
