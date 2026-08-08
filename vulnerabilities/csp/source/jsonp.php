<?php
header("Content-Type: application/json; charset=UTF-8");

// The callback is the name of a function this response will invoke in the
// caller's page, so it is taken from a fixed list rather than from the query
// string. Anything else falls back to the only callback this endpoint serves.
$allowed_callbacks = array ("solveSum");

$callback = "solveSum";
if (array_key_exists ("callback", $_GET) && in_array ($_GET['callback'], $allowed_callbacks, true)) {
	$callback = $_GET['callback'];
}

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
