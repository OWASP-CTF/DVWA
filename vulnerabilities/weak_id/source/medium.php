<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// A Unix timestamp is narrowly predictable - anyone who observes roughly when a session was
	// issued can brute-force the exact value in a handful of guesses. Use a cryptographically
	// random value instead, scoped with HttpOnly/Secure/path flags.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], true, true);
}
?>
