<?php
header("Content-Type: application/json; charset=UTF-8");

// This endpoint is only ever loaded same-origin, as
// <script src="jsonp.php?callback=...">, precisely so the strict
// "script-src 'self'" CSP on the high level will allow it to run. That
// makes whatever this response prints the one piece of script content an
// attacker can still influence on this page: taking the function name to
// invoke from the request (even after restricting it to look like a plain
// identifier) still lets an attacker name any already-defined global
// function - e.g. callback=alert - and get back alert({"answer":"15"}),
// which is arbitrary-function invocation through a supposedly locked-down
// CSP. This module only ever needs to call one function (see high.js), so,
// exactly like the impossible level's jsonp_impossible.php, the name is
// fixed here in the source rather than accepted from the caller at all.
$outp = array ("answer" => "15");

echo "solveSum(" . json_encode($outp) . ")";
?>
