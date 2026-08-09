<?php
header("Content-Type: application/json; charset=UTF-8");

// This endpoint is only ever loaded same-origin, as
// <script src="jsonp.php?callback=...">, precisely so the strict
// "script-src 'self'" CSP on the high level will allow it to run. That
// makes whatever this response prints the one piece of script content an
// attacker can still influence on this page, so the "callback" name must
// be pinned to the single function this module actually calls back into
// (see high.js) rather than merely shape-checked.
//
// A shape/character-class check (e.g. "is this a legal JS identifier?") is
// NOT enough on its own: any already-defined global function name is also
// a legal identifier, so an attacker could still set
// callback=alert (or eval, confirm, etc.) and have this endpoint hand back
// alert({"answer":"15"}) - invoking an existing function with
// attacker-chosen arguments is exactly the classic "JSONP callback
// hijacking" CSP bypass, and it never needed to smuggle in new code to do
// it. Requiring an exact match on the one legitimate callback name closes
// that off entirely.
$expectedCallback = "solveSum";

if (!array_key_exists("callback", $_GET) || $_GET['callback'] !== $expectedCallback) {
	http_response_code(400);
	header("Content-Type: application/json; charset=UTF-8");
	echo json_encode(array("error" => "Unknown callback"));
	exit;
}

$callback = $_GET['callback'];

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
