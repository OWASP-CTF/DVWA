<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// hashing a small, sequential input space is still brute-forceable; use a CSPRNG instead
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], false, false);
}

?>
