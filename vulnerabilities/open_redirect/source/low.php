<?php

// The redirect target was previously passed straight into the Location header, allowing an
// attacker to redirect victims to an arbitrary external site. index.php's own links only ever
// send "info.php?id=1" or "info.php?id=2" (a literal string, not a numeric id like
// impossible.php uses) - restrict to exactly those known-good values via an allowlist, so
// legitimate navigation keeps working while anything else is rejected.
$allowedTargets = array("info.php?id=1", "info.php?id=2");

if (array_key_exists ("redirect", $_GET) && in_array($_GET['redirect'], $allowedTargets, true)) {
	header ("location: " . $_GET['redirect']);
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
