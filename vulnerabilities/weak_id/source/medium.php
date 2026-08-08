<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Use a cryptographically secure random value instead of a value derived
	// from the clock, which an attacker can guess.
	$cookie_value = bin2hex(random_bytes(32));
	setcookie("dvwaSession", $cookie_value, [
		'expires'  => time() + 3600,
		'path'     => '/vulnerabilities/weak_id/',
		'httponly' => true,
		'samesite' => 'Strict'
	]);
}
?>
