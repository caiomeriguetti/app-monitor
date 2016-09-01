<?php

function get($arr, $key, $default) {
    
    if (!isset($arr[$key])) {
        return $default;
    }
    
    return $arr[$key];
}

$minutesToMonitor = intval(get($_GET, 'deltaMins', 60));
$index = "signals-" . date("Y") . '-' . intVal(date("m"));
$signalId = get($_GET, 'signalId', null);

$currentTime = time();
$delta = $minutesToMonitor*60;

$data = [
    'query' => [
        'bool' => [
            'filter' => [
                ['range' => [
                    'timestamp' => [
                        'gte' => $currentTime - $delta,
                        'lte' => $currentTime
                    ]
                ]],
            ]
        ]
    ]
];

if (!empty($signalId)) {
        $should = [];
        foreach ($signalId as $key => $id) {
                array_push($should, ['term' => ['signalId' => $id]]);
        }
        $data['query']['bool']['filter'][] = ['bool' => ['should' => $should]];
}


$data['size'] = 10000;
$data['sort'] = [
    ['timestamp' => 'desc']
];

$data = json_encode($data);

$url = "http://localhost:9200/$index/_search";

//open connection

$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_POSTFIELDS, $data);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);

echo $result;
