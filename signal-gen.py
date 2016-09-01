from threading import Thread
import socket
import sys
import random
import time

signalType = sys.argv[1]
signalId = sys.argv[2]

# create dgram udp socket
try:
  s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
except socket.error:
  print 'Failed to create socket'
  sys.exit()

def writeSignal(type, id):
    host = 'localhost';
    port = 6868;

    try :
        randomValue = id[1]
        if id[2] == 'rand':
            randomValue *= random.random()
        timestamp = time.time()

        msg = """{"signalId": "%s", "type": "%s", "value": %s, "timestamp": %s}"""%(id[0], type, randomValue, timestamp)
        s.sendto(msg, (host, port))
    except socket.error, msg:
        print 'Error Code : ' + str(msg[0]) + ' Message ' + msg[1]
        sys.exit()

ids = [
    ["picpay-webservice.api.addConsumer", 10, 'rand'], 
    ["picpay-webservice.api.getActivityStream", 5, 'rand'], 
    ["picpay-backend.core.ConsumerShell.processConsumerRecentInteractions", 1, 'const']
]

while True:
    for i in range(1, 1000):
        signalId = ids[random.randint(0, len(ids) -1)]
        Thread(target=writeSignal, args=(signalType, signalId)).start()
        time.sleep(0.4)
    
    

    
