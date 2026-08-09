<?php
header("Content-Type: application/json; charset=UTF-8");

if (array_key_exists ("callback", $_GET)) {
	$callback = $_GET['callback'];
} else {
	return "";
}

// The callback name is reflected verbatim into a script response - without validation an
// attacker can supply arbitrary JS (e.g. "callback=alert(document.cookie);//") which executes
// in the victim's browser. Restrict it to the character set of a valid JS identifier.
if (!preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $callback)) {
	http_response_code(400);
	echo json_encode(array("error" => "Invalid callback"));
	exit;
}

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
