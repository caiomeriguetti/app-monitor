import redis
import json
import requests
import sys

try:
    config = json.loads(open('/var/app-monitor-data/config/notifier', "r+").read())
except Exception, e:
    print "Problem load configuration", str(e)
    sys.exit(0)

r = redis.StrictRedis(host='localhost', port=6379, db=0)
maxElementsToProccessAtOnce = 100

while True:

    elementsToProcess = []
    stopPopping = False
    poppeds = 0

    while poppeds < maxElementsToProccessAtOnce and stopPopping == False:

        queuedElement = r.lpop("app-monitor-notification")

        if queuedElement:
            elementsToProcess.append(queuedElement)
            poppeds += 1
        else:
            stopPopping = True

    if poppeds == 0:
        time.sleep(1)

    for element in elementsToProcess:
        try:
            notificationData = json.loads(element)
        except:
            print "Failed to parse json notification", element
            continue

        alert = notificationData['alertData']
        statusFrom = notificationData['statusFrom']
        statusTo = notificationData['statusTo']

        message = """ Alert %s changed state: %s -> %s""" % (alert['id'], statusFrom, statusTo)
        
        payload = {
            'text': message,
            'username': config["SLACK_NOTIFICATION_USERNAME"],
            'icon_emoji': config["SLACK_NOTIFICATION_ICON_EMOJI"],
            'channel': config["SLACK_NOTIFICATION_CHANNEL"]
        }
        
        jsonPayload = "payload=" + json.dumps(payload)
        
        requests.post(config["SLACK_NOTIFICATION_ENDPOINT"], data=jsonPayload)

    time.sleep(1)