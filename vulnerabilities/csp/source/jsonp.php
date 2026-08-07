<?php
header("Content-Type: application/json; charset=UTF-8");

if (!array_key_exists ("callback", $_GET)) {
	return "";
}

// The callback name is hardcoded rather than taken from the request, so an
// attacker cannot have arbitrary JavaScript echoed back same-origin.
$outp = array ("answer" => "15");

echo "solveSum (".json_encode($outp).")";
?>
