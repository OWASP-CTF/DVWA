<?php
header("Content-Type: application/json; charset=UTF-8");

// Echoing $_GET['callback'] turned this same-origin endpoint into a script
// gadget: anything the caller put in the parameter was served back as
// executable JavaScript, straight through a "script-src 'self'" policy.
// The function to invoke is fixed, which is all high.js ever asked for.
$outp = array ("answer" => "15");

echo "solveSum(" . json_encode($outp) . ")";
?>
