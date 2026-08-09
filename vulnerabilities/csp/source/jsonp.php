<?php
header("Content-Type: application/json; charset=UTF-8");

// The whole point of the strict "script-src 'self'" CSP on the high level is
// that only same-origin scripts can run. This endpoint is loaded as a
// same-origin <script src="...jsonp.php?callback=..."> though, so if the
// callback name were echoed back verbatim it would let an attacker smuggle
// arbitrary JavaScript straight through the CSP (e.g.
// callback=alert(document.cookie);// ). A JSONP callback is only ever used
// as a bare identifier to invoke a function, so it must be restricted to
// that shape before being reflected back into a script response.
if (array_key_exists ("callback", $_GET) && preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $_GET['callback'])) {
	$callback = $_GET['callback'];
} else {
	http_response_code(400);
	echo json_encode(array("error" => "invalid callback"));
	return;
}

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
