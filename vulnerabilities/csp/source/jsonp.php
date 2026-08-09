<?php
header("Content-Type: application/json; charset=UTF-8");

// The CSP on this module only restricts which *origins* scripts can be
// loaded from - it says nothing about what a same-origin script is allowed
// to contain. Validating that "callback" merely *looks like* a JS identifier
// is not enough: "alert", "confirm", "print" and friends are all perfectly
// well-formed identifiers that also happen to be real, already-defined
// global functions, so ?callback=alert still turns
// <script src="jsonp.php?callback=alert"></script> - a same-origin load a
// strict script-src 'self' CSP happily allows - into a way to pop
// arbitrary content with our JSON body as the argument. The only genuine
// caller of this endpoint (the "Solve the sum" button) always wants the
// same function invoked, so - exactly like the impossible level's
// jsonp_impossible.php - the name is hardcoded and the request can no
// longer choose it at all.
$outp = array ("answer" => "15");

echo "solveSum(" . json_encode($outp) . ")";
?>
