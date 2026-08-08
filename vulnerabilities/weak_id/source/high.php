<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Hashing a small, sequential, guessable input (an incrementing
	// counter) still only has as much entropy as that input - an attacker
	// can just hash 1, 2, 3... and match the cookie. Use a cryptographically
	// secure random value instead.
	$cookie_value = bin2hex(random_bytes(20));

	// The cookie itself used to be set with Secure and HttpOnly both off,
	// and its domain taken from the caller-controlled Host header. A cookie
	// without HttpOnly is readable by any script on the page, so a random
	// value only protects against guessing, not theft via XSS elsewhere on
	// the site; a domain sourced from the request lets a spoofed Host widen
	// where the cookie gets sent. Scope the cookie to this host implicitly
	// (leave domain empty) and mark it Secure, HttpOnly and SameSite=Strict.
	setcookie("dvwaSession", $cookie_value, array(
		'expires'  => time() + 3600,
		'path'     => "/vulnerabilities/weak_id/",
		'secure'   => true,
		'httponly' => true,
		'samesite' => 'Strict',
	));
}

?>
