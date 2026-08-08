<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Session identifiers must be unpredictable, so take them from the CSPRNG
	// rather than from a counter or the clock.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], true, true);
}
?>
