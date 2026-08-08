<?php

/*
 * Reflecting a caller supplied callback name into the response body turns this
 * endpoint into an arbitrary script generator on our own origin, which defeats
 * a script-src 'self' policy. Only the callbacks this application actually
 * uses are accepted.
 */

header("Content-Type: application/json; charset=UTF-8");

$allowed_callbacks = array ("solveSum");

if (array_key_exists ("callback", $_GET) && is_string ($_GET['callback']) &&
	in_array ($_GET['callback'], $allowed_callbacks, true)) {
	$callback = $_GET['callback'];
} else {
	http_response_code (400);
	echo json_encode (array ("error" => "Unknown callback"));
	exit;
}

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
