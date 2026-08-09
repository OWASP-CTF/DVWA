<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (!isset ($_SESSION['last_session_id'])) {
		$_SESSION['last_session_id'] = 0;
	}
	$_SESSION['last_session_id']++;
	$cookie_value = bin2hex(random_bytes(32));
	setcookie("dvwaSession", $cookie_value, ['httponly' => true, 'secure' => true, 'samesite' => 'Strict']);
}
?>
