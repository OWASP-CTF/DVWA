<?php
header("Content-Type: application/javascript; charset=UTF-8");

$callback = $_GET['callback'] ?? '';
if (!hash_equals('solveSum', $callback)) {
	http_response_code(400);
	exit;
}

$outp = array ("answer" => "15");

echo "solveSum(" . json_encode($outp) . ");";
?>
