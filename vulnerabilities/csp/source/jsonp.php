<?php
// This response is loaded through a <script> element, so it must be
// served as JavaScript. With X-Content-Type-Options: nosniff a JSON
// content type is refused by the browser and the callback never runs.
header("Content-Type: application/javascript; charset=UTF-8");

/*
 * The callback name used to be taken from the query string and written into
 * the response, which let a caller emit arbitrary JavaScript from this origin
 * and walk straight through a script-src 'self' policy.
 *
 * The function name is fixed, exactly as in jsonp_impossible.php.
 */

$outp = array ("answer" => "15");

echo "solveSum (".json_encode($outp).")";
?>
