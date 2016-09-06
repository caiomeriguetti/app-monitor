import redis
import json
import requests
import sys
import time

while True:
    try:
        config = json.loads(open('/var/app-monitor-data/config/notifier', "r+").read())
        break
    except Exception, e:
        print "Problem loading configuration", str(e)
        time.sleep(3)

while True:
    try:
        r = redis.StrictRedis(host='localhost', port=6379, db=0)
        break
    except:
        print "Error connecting to redis"
        time.sleep(3)


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
        continue

    for element in elementsToProcess:
        try:
            notificationData = json.loads(element)
        except:
            print "Failed to parse json notification", element
            continue

        alert = notificationData['alertData']
        statusFrom = notificationData['statusFrom']
        statusTo = notificationData['statusTo']
        signalId = notificationData['signalId']

        try:
            if statusTo == "MATCH_ALERT_CRITERIA":
                message = """ Alert %s: %s""" % (signalId, alert['match_text'])
            elif statusTo == "OK":
                message = """ Alert %s: %s""" % (signalId, alert['notmatch_text'])
        except:
            message = "Alert %s(%s) changed state from %s to %s." % (signalId, alertData['id'], statusFrom, statusTo)
        
        try:
            payload = {
                'text': message,
                'username': config["SLACK_NOTIFICATION_USERNAME"],
                'icon_emoji': config["SLACK_NOTIFICATION_ICON_EMOJI"],
                'channel': config["SLACK_NOTIFICATION_CHANNEL"]
            }
            
            requests.post(config["SLACK_NOTIFICATION_ENDPOINT"], data={'payload': json.dumps(payload)})
        except Exception, e:
            print "Error sending notification to Slack ", str(e)

    time.sleep(1)