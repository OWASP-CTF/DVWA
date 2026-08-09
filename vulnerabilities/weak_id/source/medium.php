<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// CSPRNG instead of a timestamp, which is trivially guessable
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value);
}
?>
