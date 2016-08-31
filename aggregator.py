import redis
import time
import requests
import json
import datetime

r = redis.StrictRedis(host='localhost', port=6379, db=0)
maxElementsToProccessAtOnce = 10000

byIdAggregation = {}

while True:

    now = datetime.datetime.now()

    indexName = 'signals-' + str(now.year) + "-" + str(now.month)

    response = requests.get('http://localhost:9200/' + indexName)
    
    if response.status_code == 404:
        mappings = {
          "mappings": {
             
          }
        }

        mappings['mappings'][indexName] = {
          "properties": {
              "signalId": {
                "type": "string",
                "index": "not_analyzed"
              }
          }
        }

        requests.put('http://localhost:9200/' + indexName, data=json.dumps(mappings))
        

    elementsToProcess = []
    stopPopping = False
    poppeds = 0

    while poppeds < maxElementsToProccessAtOnce and stopPopping == False:

        queuedElement = r.lpop("app-monitor-queue")

        if queuedElement:
            elementsToProcess.append(queuedElement)
            poppeds += 1
        else:
            stopPopping = True

    if poppeds == 0:
        time.sleep(1)

    for element in elementsToProcess:

        try:
            elementData = json.loads(element)
        except:
            continue
        if not("signalId" in elementData.keys()):
            continue
        id = elementData["signalId"]
        value = elementData["value"]
        ts = elementData["timestamp"]
        ts = ts - (ts % 30)
        tsStr = str(ts)
        type = elementData["type"]

        if not(id in byIdAggregation.keys()) or byIdAggregation[id] == None:
            byIdAggregation[id] = {}

        if not(tsStr in byIdAggregation[id].keys()) or byIdAggregation[id][tsStr] == None:
            byIdAggregation[id][tsStr] = {"signalId": id, "type": type, "valueSum": value, "elementsNumber": 1, "timestamp": ts}
            continue
        else:
            byIdAggregation[id][tsStr]["valueSum"] += value
            byIdAggregation[id][tsStr]["elementsNumber"] += 1

    byIdAggregationKeys = byIdAggregation.keys()
    if len(byIdAggregationKeys) > 0:
        for id in byIdAggregationKeys:
            if len(byIdAggregation[id].keys()):
                for tsStr in byIdAggregation[id].keys():
                    if time.time() - byIdAggregation[id][tsStr]["timestamp"] >= 30:
                        #send to elastic
                        print "Sending to elasticsearch", id, byIdAggregation[id][tsStr]
                        response = requests.post('http://localhost:9200/%s/log/'%(indexName,), data=json.dumps(byIdAggregation[id][tsStr]))
                        del byIdAggregation[id][tsStr]

    time.sleep(1)