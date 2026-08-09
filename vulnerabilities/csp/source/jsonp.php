<?php
header("Content-Type: application/json; charset=UTF-8");

// The CSP on this module only restricts which *origins* scripts can be
// loaded from - it says nothing about what a same-origin script is allowed
// to contain. Echoing an attacker-chosen "callback" value straight back as
// executable code turns this same-origin, CSP-trusted endpoint into a way to
// run arbitrary JavaScript (e.g. ?callback=alert(document.cookie);// ),
// completely sidestepping the CSP. A JSONP callback name is only ever
// supposed to be a plain JavaScript identifier, so anything else is
// rejected instead of being reflected back into the response.
if (array_key_exists ("callback", $_GET) && preg_match ('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $_GET['callback'])) {
	$callback = $_GET['callback'];
} else {
	http_response_code (400);
	echo json_encode (array ("error" => "Invalid callback"));
	exit;
}

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
