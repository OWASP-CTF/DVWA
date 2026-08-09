<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// A raw incrementing counter is trivial to predict/enumerate (session N+1
	// or N-1 is just another user's cookie). Use an unpredictable value instead.
	$cookie_value = bin2hex(random_bytes(16));
	setcookie("dvwaSession", $cookie_value);
}
?>
