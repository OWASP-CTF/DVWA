<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Hashing a predictable, low-entropy seed (an incrementing counter)
	// doesn't add any real unpredictability - md5(1), md5(2), md5(3)... is
	// just as enumerable as the raw counter, only slightly obfuscated. Use
	// a cryptographically secure random value instead.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], false, false);
}

?>
