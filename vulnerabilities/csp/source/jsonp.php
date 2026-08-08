<?php
header("Content-Type: application/json; charset=UTF-8");

// The "callback" parameter used to be echoed back verbatim, turning this
// same-origin JSONP endpoint into a script gadget: since CSP allows
// script-src 'self', anyone able to inject an external <script src>
// pointing here could set callback to arbitrary JavaScript and run it
// despite the policy. A same-origin endpoint must never let a caller
// choose the code it emits, so the callback name is fixed.
$outp = array ("answer" => "15");

echo "solveSum" . "(".json_encode($outp).")";
?>
