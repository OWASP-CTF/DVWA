<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// This level hashed an incrementing counter. Hashing a value that only ever
	// takes a few thousand possibilities just means enumerating the hashes.
	//
	// The value comes from a cryptographically secure random source, so nothing
	// about one id says anything about the next. It is scoped to this module,
	// marked HttpOnly so script cannot read it, and marked Secure whenever the
	// request arrived over TLS.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", "", dvwaIsHttps(), true);
}
?>
