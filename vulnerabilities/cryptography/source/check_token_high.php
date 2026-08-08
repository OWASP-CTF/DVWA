<?php

require_once ("token_library_high.php");

header ("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] != "POST") {
	print json_encode (array (
					"status" => 405,
					"message" => "Method not supported"
				));
	exit;
}

// array_key_exists first: a request with no Content-Type header used to reach
// straight into $_SERVER and warn (CWE-234 Failure to Handle Missing
// Parameter).
if (!array_key_exists ("CONTENT_TYPE", $_SERVER) || strpos ($_SERVER['CONTENT_TYPE'], "application/json") !== 0) {
	print json_encode (array (
					"status" => 527,
					"message" => "Content type must be application/json"
				));
	exit;
}

print check_token (file_get_contents('php://input'));
exit;
