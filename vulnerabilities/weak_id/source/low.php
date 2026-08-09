<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
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
