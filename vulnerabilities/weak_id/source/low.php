<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Generate secure random session ID using CSPRNG
	$cookie_value = bin2hex(random_bytes(32));
	setcookie("dvwaSession", $cookie_value, [
		'expires' => time() + 3600,
		'path' => '/vulnerabilities/weak_id/',
		'samesite' => 'Strict'
	]);
}
?>
