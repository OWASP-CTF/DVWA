<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// SECURE FIX: Generate a cryptographically secure random session token
	// instead of deriving it from the current server timestamp, which is a
	// narrow and guessable input an attacker can enumerate.
	$cookie_value = bin2hex(random_bytes(32));

	// Set secure cookie with HttpOnly and SameSite flags
	setcookie(
		"dvwaSession",
		$cookie_value,
		[
			'expires'  => time() + 3600,
			'path'     => '/vulnerabilities/weak_id/',
			'domain'   => $_SERVER['HTTP_HOST'],
			'secure'   => true,
			'httponly' => true,
			'samesite' => 'Strict'
		]
	);
}
?>
