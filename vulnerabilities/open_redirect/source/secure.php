<?php

/*
The redirect target is never taken from the request. The parameter is only ever
used to look up one of the two quote pages this page is allowed to send a user
to, so an attacker supplied absolute URL, protocol relative URL or crafted
path can never reach the Location header (CWE-601).
*/

$allowedTargets = array(
	"1"             => "info.php?id=1",
	"2"             => "info.php?id=2",
	"info.php?id=1" => "info.php?id=1",
	"info.php?id=2" => "info.php?id=2",
);

$target = "";

if (array_key_exists("redirect", $_GET) && is_string($_GET['redirect'])
	&& array_key_exists($_GET['redirect'], $allowedTargets)) {
	$target = $allowedTargets[$_GET['redirect']];
}

if ($target !== "") {
	header("location: " . $target);
	exit;
}

http_response_code(500);
?>
<p>Unknown redirect target.</p>
<?php
exit;
?>
