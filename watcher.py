from __future__ import division
import requests
import json
import time

alerts = {
	"picpay-webservice.api.addConsumer":[
		{'id': 'addconsumer-gte5-for1min', 'name': 'addConsumer demorando mais de 5', 'gte': 5, 'for': 2},
		{'id': 'addconsumer-lte2-for1min', 'name': 'addConsumer demorando menos de 2', 'lte': 2, 'for': 2}
	]
}

alertStates = {}

while True:

	signalsToWatch = alerts.keys()

	currentTime = time.time() - 60

	mins = 60

	timePoint = currentTime - (currentTime % 30)
	
	timeline = []
	timelineMap = {}
	while timePoint > currentTime - (mins*60):
		timePoint = int(timePoint)
		timeline.append(timePoint)
		timePoint -= 30

	data={
		"query": {
			"bool": {
				"filter": [
					{"range": {
						"timestamp": {
							"gte": currentTime - (mins*60),
							"lte": currentTime
						}
					}}
				]
			}
		}
	}

	should = []
	for signal in signalsToWatch:
		should.append({'term': {'signalId': signal}})
	
	data['query']['bool']['filter'].append({'bool': {'should':should}})

	data['size'] = 10000
	data['sort'] = [
		{'timestamp': 'desc'}
	]

	response = requests.post('http://localhost:9200/time-spent/_search', data=json.dumps(data))

	responseData = json.loads(response.text)

	for aggregation in responseData['hits']['hits']:
		timestamp = int(aggregation['_source']['timestamp'])
		timestampStr = str(timestamp)
		signalId = aggregation['_source']['signalId']

		if not(signalId in timelineMap.keys()):
			timelineMap[signalId] = {}

		if not(timestampStr in timelineMap[signalId].keys()):
			timelineMap[signalId][timestampStr] = []

		timelineMap[signalId][timestampStr].append(aggregation)

	for alertSignalId in alerts.keys():
		alertsOfSignal = alerts[alertSignalId]

		for alert in alertsOfSignal:

			numTimePointsEvaluated = 0
			numTimePointsMatched = 0
			alertId = alert['id']

			if not(alertId in alertStates.keys()):
				alertStates[alertId] = None

			alertKeys = alert.keys()

			if not(alertSignalId in timelineMap.keys()):
				timelineMap[alertSignalId] = {}

			signalsToEvaluate = timelineMap[alertSignalId]


			for timePoint in timeline:
				numTimePointsEvaluated += 1

				timePointStr = str(timePoint)

				if not(timePointStr in signalsToEvaluate.keys()):
					average = 0
				else:
					signalsOnTimePoint = signalsToEvaluate[timePointStr]

					averageSum = 0

					for aggregation in signalsOnTimePoint:
						valueSum = aggregation['_source']['valueSum']
						elementsNumber = aggregation['_source']['elementsNumber']
						timestamp = aggregation['_source']['timestamp']
						type = aggregation['_source']['type']
						aggAverage = valueSum / elementsNumber
						averageSum += aggAverage

					average = averageSum / len(signalsOnTimePoint)

				if 'gte' in alertKeys:

					if average >= alert['gte']:
						numTimePointsMatched += 1

				elif 'lte' in alertKeys:

					if average <= alert['lte']:
						numTimePointsMatched += 1

				if timeline[0] - timePoint > alert['for']*60:
					break

			if numTimePointsEvaluated == numTimePointsMatched and alertStates[alertId] != 'MATCH_ALERT_CRITERIA':
				alertStates[alertId] = 'MATCH_ALERT_CRITERIA'
				print 'Alert state changed', alertId, alertStates[alertId]
			elif numTimePointsEvaluated != numTimePointsMatched and alertStates[alertId] != 'OK':
				alertStates[alertId] = 'OK'
				print 'Alert state changed', alertId, alertStates[alertId]



	time.sleep(1)