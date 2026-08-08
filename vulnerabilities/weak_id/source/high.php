<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Session IDs must be unpredictable: a hash of an incrementing counter is
	// trivially reversible, so use a CSPRNG and set the cookie HttpOnly.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", "", !empty($_SERVER['HTTPS']), true);
}

?>
