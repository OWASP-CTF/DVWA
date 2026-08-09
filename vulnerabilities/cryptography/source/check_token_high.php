<?php

require_once ("token_library_high.php");

$ret = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$content_type = array_key_exists ('CONTENT_TYPE', $_SERVER) ? $_SERVER['CONTENT_TYPE'] : "";
	if ($content_type != "application/json") {
		$ret = json_encode (array (
						"status" => 527,
						"message" => "Content type must be application/json"
					));
	} else {
		$token = $jsonData = file_get_contents('php://input');
		$ret = check_token ($token);
	}
} else {
	$ret = json_encode (array (
					"status" => 405,
					"message" => "Method not supported"
				));
}

print $ret;
exit;
