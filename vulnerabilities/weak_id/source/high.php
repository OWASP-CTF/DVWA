<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Hashing a predictable counter does not add any entropy, so use a
	// cryptographically secure random value instead.
	$cookie_value = bin2hex(random_bytes(32));
	setcookie("dvwaSession", $cookie_value, [
		'expires'  => time() + 3600,
		'path'     => '/vulnerabilities/weak_id/',
		'httponly' => true,
		'samesite' => 'Strict'
	]);
}

?>
