<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Session IDs must be unpredictable: use a CSPRNG rather than the current time.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", "", !empty($_SERVER['HTTPS']), true);
}
?>
