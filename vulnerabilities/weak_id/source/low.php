<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Session identifiers must be unpredictable, so take them from the CSPRNG
	// rather than from a counter or the clock.
	$cookie_value = bin2hex(random_bytes(20));
	// Only mark the cookie Secure when the connection actually is, otherwise
	// the browser would drop it and the lab would stop working.
	$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], $secure, true);
}
?>
