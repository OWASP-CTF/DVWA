<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Hashing a counter does not add entropy -- md5(1), md5(2), md5(3) are
	// all precomputable. Issue an unpredictable value from the CSPRNG
	// instead, and keep it out of reach of client-side script.
	$cookie_value = bin2hex( random_bytes( 20 ) );
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], false, true);
}

?>
