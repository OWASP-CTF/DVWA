<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Cryptographically secure random session identifier (was: predictable time() value).
	$cookie_value = bin2hex(random_bytes(20));
	// See low.php for why "secure" is intentionally left off over this plain-HTTP lab.
	setcookie("dvwaSession", $cookie_value, time() + 3600, "", "", false, true);
}
?>
