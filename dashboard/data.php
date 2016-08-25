<?php

function get($arr, $key, $default) {
	
	if (!isset($arr[$key])) {
		return $default;
	}
	
	return $arr[$key];
}

$minutesToMonitor = intval(get($_GET, 'deltaMins', 60));
$index = get($_GET, 'index', 'time-spent');

$currentTime = time();
$delta = $minutesToMonitor*60;

$data = array('query' => array(
        'range' => array(
                'timestamp' => array(
                        'gte' => $currentTime - $delta,
                        'lte' => $currentTime
                )
        )
));

$url = "http://localhost:9200/$index/_search";

//open connection

$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($data));

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);

echo $result;
