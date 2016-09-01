import socket
import sys
from threading import Thread
import requests
import json
import redis

r = redis.StrictRedis(host='localhost', port=6379, db=0)

# Create a TCP/IP socket
sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)

# Bind the socket to the port
server_address = ('0.0.0.0', 10000)
print >>sys.stderr, 'starting up on %s port %s' % server_address
sock.bind(server_address)

def handle(jsonStr):
    r.rpush("app-monitor-queue", jsonStr)

while True:
    print >>sys.stderr, '\nwaiting to receive message'

    try:
        jsonStr, address = sock.recvfrom(1024)
        Thread(target=handle, args=(jsonStr,)).start()
    except:
        print "Error recieving message"

    print >>sys.stderr, 'received %s bytes from %s' % (len(jsonStr), address)
    print >>sys.stderr, jsonStr
