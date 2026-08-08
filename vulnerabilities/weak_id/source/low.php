<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Cryptographically secure random session identifier (was: predictable incrementing counter).
	$cookie_value = bin2hex(random_bytes(20));
	// Note: intentionally not using the "secure" flag here (unlike impossible.php) because the
	// lab is served over plain HTTP -- a browser will silently refuse to store/send back a
	// Secure-flagged cookie on a non-HTTPS origin, which would stop this legitimate cookie from
	// being issued/observable. httponly is safe to keep on since it only affects JS access.
	setcookie("dvwaSession", $cookie_value, time() + 3600, "", "", false, true);
}
?>
