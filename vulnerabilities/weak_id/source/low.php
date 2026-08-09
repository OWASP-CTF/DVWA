<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Use a cryptographically secure random value so the id cannot be
	// predicted or incremented from a previously issued one.
	$cookie_value = bin2hex(random_bytes(32));
	setcookie("dvwaSession", $cookie_value, [
		'expires'  => time() + 3600,
		'path'     => '/vulnerabilities/weak_id/',
		'httponly' => true,
		'samesite' => 'Strict'
	]);
}
?>
