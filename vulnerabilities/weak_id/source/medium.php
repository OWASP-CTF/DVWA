<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// time() only has second resolution and is trivially guessable/brute
	// forceable (it's public knowledge, give or take clock skew). Use an
	// unpredictable value instead.
	$cookie_value = bin2hex(random_bytes(16));
	setcookie("dvwaSession", $cookie_value);
}
?>
