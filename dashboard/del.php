<?php

include 'config.php';

$name = $_POST['name'];
$filteredName = str_replace(" ", "-", strtolower($name));

$dir = $CONFIG['data-dir'];

$path = $dir . $filteredName;

if (file_exists($path)) {
	unlink($path);
	echo json_encode(['success' => 1]);
} else {
	http_response_code(500);
	echo json_encode(['success' => 0]);
}
