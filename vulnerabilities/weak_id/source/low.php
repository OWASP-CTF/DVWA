<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// A simple incrementing counter is trivially predictable - anyone who
	// has ever seen one valid session ID can guess every other one. Use a
	// cryptographically secure random value instead.
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value);
}
?>
