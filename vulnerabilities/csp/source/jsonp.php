<?php
header("Content-Type: application/json; charset=UTF-8");

// The name of the function this response invokes in the caller's page is fixed
// here and is never taken from the request, so nothing a caller sends can end
// up being executed as script by the page that includes this endpoint. This is
// what the impossible level's jsonp_impossible.php does.

$outp = array ("answer" => "15");

echo "solveSum (".json_encode($outp).")";
?>
