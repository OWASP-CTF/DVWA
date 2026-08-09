<?php
header("Content-Type: application/json; charset=UTF-8");

if (array_key_exists ("callback", $_GET)) {
	$callback = $_GET['callback'];
} else {
	return "";
}

# Only the callback this module actually uses may be echoed back, otherwise the
# response body is attacker controlled JavaScript served from this origin.
if ($callback !== "solveSum") {
	return "";
}

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
