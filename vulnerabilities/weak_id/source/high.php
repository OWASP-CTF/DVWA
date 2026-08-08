<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Cryptographically secure random session identifier (was: md5() of a predictable counter).
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $_SERVER['HTTP_HOST'], false, false);
}

?>
