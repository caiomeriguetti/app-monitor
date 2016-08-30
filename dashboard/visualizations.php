<?php

include 'config.php';

$dir = $CONFIG['data-dir'];

$files = scandir($dir);

$response = [];
foreach ($files as $key => $file) {
	$path = $dir . $file;
	try {
		$contents = json_decode(file_get_contents($path), true);
		
		if (empty($contents)) {
			continue;
		}

		array_push($response, $contents);
	} catch (Exception $e) {
		continue;
	}
}

http_response_code(200);
echo json_encode($response);