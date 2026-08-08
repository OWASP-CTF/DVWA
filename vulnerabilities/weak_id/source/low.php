<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$cookie_value = bin2hex(random_bytes(20));
	setcookie("dvwaSession", $cookie_value);
}
?>
