<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Hashing a small, sequential, guessable input (an incrementing
	// counter) still only has as much entropy as that input - an attacker
	// can just hash 1, 2, 3... and match the cookie. Use a cryptographically
	// secure random value instead.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], false, false);
}

?>
