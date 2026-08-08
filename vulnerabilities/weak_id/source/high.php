<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$cookie_value = bin2hex(random_bytes(32));
	setcookie("dvwaSession", $cookie_value, [
		"expires" => time() + 3600,
		"path" => "/vulnerabilities/weak_id/",
		"secure" => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off",
		"httponly" => true,
		"samesite" => "Strict",
	]);
}

?>
