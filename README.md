# About the project

This is a signals monitoring app. It was created to monitor app performance and health using signals.
You can also configure simple alerts and be notified when some signal match a predefined condition.

Example of alert:

```json

{
	"signal.name.example":[
		{"id": "signal-average-gte5-for2mins", "gte": 5, "for": 2},
		{"id": "signal-average-lte1-for2mins", "lte": 1, "for": 2}
	]
}


```

# About the code

The project consists of some python daemons that work together. To keep all python daemons running we use supervisor.

The udp-server.py recieves all signals and queue them in redis. Then, the aggregator pop the signals from redis, aggregates them into the right time point and then save the aggregations into elasticsearch.

The watcher.py keeps looking to the signals that are saved into elasticsearch and check the state of the signals over time to see if there is any signal that match an state described in some alert. If there is any signal matching an alert condition, a notification is queued in redis. To configure alerts, just create the file /var/app-monitor-data/config/alerts in the host of the docker container.

The notifier.py pops notifications from redis and do the job of sending them to a slack channel. To configure notifier with proper slack configs you should create the file /var/app-monitor-data/config/notifier in the host of the docker container.


# Deploy

- instal docker: https://docs.docker.com/engine/installation/
- clone the repo https://github.com/caiomeriguetti/app-monitor.git

```bash

cd [cloned-repo-dir]/docker-container
./build-image.sh
./run-container.sh

```

- Make an udp request to localhost:6868 with the content: 

```json
{"signalId": "<ID_OF_YOUR_SIGNAL>", "type": "<TYPE_OF_YOUR_SIGNAL>", "value": <FLOAT_VALUE>, "timestamp": <TIMESTAMP_SECONDS>}

```