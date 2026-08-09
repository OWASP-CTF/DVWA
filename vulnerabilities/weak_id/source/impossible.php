<?php

function weakIdCookieSecure() {
	return !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off';
}

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], weakIdCookieSecure(), true);
}
?>
