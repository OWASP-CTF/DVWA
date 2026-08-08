<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// A Unix timestamp has a tiny search space (seconds since epoch, and an
	// attacker usually knows roughly when the session was issued), so it's
	// effectively brute-forceable. Use a cryptographically secure random
	// value instead.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value);
}
?>
