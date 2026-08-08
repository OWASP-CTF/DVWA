<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Hashing a small, sequential, guessable input (an incrementing
	// counter) still only has as much entropy as that input - an attacker
	// can just hash 1, 2, 3... and match the cookie. Use a cryptographically
	// secure random value instead.
	$cookie_value = bin2hex(random_bytes(20));

	// The cookie itself used to be set with Secure and HttpOnly both off. A
	// cookie without HttpOnly is readable by any script on the page, so a
	// random value only protects against guessing, not theft via XSS
	// elsewhere on the site. Only mark it Secure when the connection
	// actually is HTTPS - a Secure cookie sent over plain HTTP is silently
	// dropped by the browser, which would break the lab entirely rather
	// than harden it.
	$secure = ( !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' );
	setcookie("dvwaSession", $cookie_value, array(
		'expires'  => time() + 3600,
		'path'     => "/vulnerabilities/weak_id/",
		'secure'   => $secure,
		'httponly' => true,
		'samesite' => 'Strict',
	));
}

?>
