<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// md5() of a small, predictable, monotonically-incrementing counter is still fully
	// brute-forceable - an attacker just hashes 1, 2, 3... themselves. Also missing the
	// Secure/HttpOnly flags. Use a cryptographically random value with both flags set.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], true, true);
}

?>
