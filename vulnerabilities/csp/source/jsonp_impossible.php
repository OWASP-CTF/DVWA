<?php
// This response is loaded through a <script> element, so it must be
// served as JavaScript. With X-Content-Type-Options: nosniff a JSON
// content type is refused by the browser and the callback never runs.
header("Content-Type: application/javascript; charset=UTF-8");

$outp = array ("answer" => "15");

echo "solveSum (".json_encode($outp).")";
?>
