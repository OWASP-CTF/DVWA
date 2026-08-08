<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// A Unix timestamp is public knowledge, so it identifies nobody. Issue an
	// unpredictable value from the CSPRNG instead, and keep it out of reach
	// of client-side script.
	$cookie_value = bin2hex( random_bytes( 20 ) );
	setcookie("dvwaSession", $cookie_value, 0, "", "", false, true);
}
?>
