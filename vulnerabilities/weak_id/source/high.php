<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Cryptographically secure random session identifier (was: md5() of a predictable counter).
	$cookie_value = bin2hex(random_bytes(20));
	// Do not derive a Domain attribute from the client-controlled Host header.
	// A host-only cookie also works on development origins that include a port.
	setcookie("dvwaSession", $cookie_value, [
		'expires'  => time() + 3600,
		'path'     => '/vulnerabilities/weak_id/',
		'secure'   => false,
		'httponly' => true,
		'samesite' => 'Strict',
	]);
}

?>
