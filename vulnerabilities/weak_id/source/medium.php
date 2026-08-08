<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// A Unix timestamp has a tiny search space (seconds since epoch, and an
	// attacker usually knows roughly when the session was issued), so it's
	// effectively brute-forceable. Use a cryptographically secure random
	// value instead.
	$cookie_value = bin2hex(random_bytes(20));
	// HttpOnly so client-side script can never read the value back out (a
	// strong ID is still worth nothing if it can be stolen via XSS). Only
	// mark it Secure when the connection actually is HTTPS, otherwise the
	// browser silently drops the cookie and the lab stops working.
	$secure = !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off';
	setcookie("dvwaSession", $cookie_value, time() + 3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], $secure, true);
}
?>
