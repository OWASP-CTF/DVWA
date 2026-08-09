<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// CTF: Challenge-9-Weak-Session-IDs-Low | OWASP A07:2025 Authentication Failures
	// Replace the predictable session counter with an independently random identifier.
	$cookie_value = bin2hex(random_bytes(32));
	setcookie("dvwaSession", $cookie_value, array (
		"expires" => time() + 3600,
		"path" => "/vulnerabilities/weak_id/",
		"secure" => true,
		"httponly" => true,
		"samesite" => "Strict"
	));
}
?>
