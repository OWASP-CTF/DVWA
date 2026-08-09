<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// A monotonically-incrementing integer is fully guessable/enumerable - anyone could predict
	// or brute-force another user's session identifier. Use a cryptographically random value
	// instead, scoped with HttpOnly/Secure/path flags so it can't be read or replayed cross-path.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], true, true);
}
?>
