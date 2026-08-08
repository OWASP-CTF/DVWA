<?php
header("Content-Type: application/json; charset=UTF-8");

// Strict allow-list: the page never legitimately needs any callback name other
// than "solveSum", so anything else is ignored rather than reflected verbatim.
// This prevents the callback parameter being used to smuggle arbitrary
// JavaScript into a same-origin ('self') script response.
$allowedCallbacks = array( 'solveSum' );

$callback = 'solveSum';
if ( array_key_exists( 'callback', $_GET ) && in_array( $_GET['callback'], $allowedCallbacks, true ) ) {
	$callback = $_GET['callback'];
}

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
