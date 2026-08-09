<?php
header("Content-Type: application/json; charset=UTF-8");

// This endpoint is only ever loaded same-origin as <script src="jsonp.php?...">,
// precisely so the strict "script-src 'self'" CSP on this module will allow it
// to run. That makes whatever this response prints the one piece of script
// content an attacker can still influence: taking the function name to invoke
// from the request - even restricted to look like a plain identifier - still
// lets an attacker name any already-defined global function (e.g.
// callback=alert) and get back alert({"answer":"15"}), arbitrary-function
// invocation through an otherwise locked-down CSP. This module only ever
// calls one function, so, exactly like the impossible level's
// jsonp_impossible.php, the name is fixed here rather than taken from the
// caller at all.
$outp = array ("answer" => "15");

echo "solveSum(" . json_encode($outp) . ")";
?>
