<?php

// Blocking only "http://"/"https://" doesn't stop a protocol-relative URL like "//evil.com",
// which browsers still treat as an absolute redirect to an attacker-controlled host. index.php's
// own links only ever send "info.php?id=1" or "info.php?id=2" - restrict to exactly those
// known-good values via an allowlist, so legitimate navigation keeps working while anything
// else (including protocol-relative payloads) is rejected.
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
