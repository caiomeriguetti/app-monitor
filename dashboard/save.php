<?php

include 'config.php';

try {

	try {
		$data = json_decode($_POST['visualization-data'], true);
	} catch (Exception $e) {
		http_response_code(400);
		throw new InvalidArgumentException("Invalid visualization data");
	}

	if (empty($data["name"])) {
		http_response_code(400);
		throw new InvalidArgumentException("Name cannot be empty");
	}

	$filteredName = str_replace(" ", "-", strtolower($data["name"]));

	$dirToSave = $CONFIG['data-dir'];

	if (!file_exists($dirToSave)) {
		mkdir($dirToSave);
	}

	file_put_contents($dirToSave . $filteredName, $_POST['visualization-data']);

	http_response_code(200);
	echo json_encode(["saved" => 1, "name" => $filteredName]);

} catch (Exception $e) {
	echo json_encode(["saved" => 0, "error" => $e -> getMessage()]);
}