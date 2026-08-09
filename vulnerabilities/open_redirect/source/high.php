<?php

// strpos() only checked that "info.php" appears *anywhere* in the value - a payload like
// "//evil.com/?info.php" or "https://evil.com/#info.php" still satisfies the check while
// redirecting off-site, since the Location header is taken from the whole string, not just the
// matched substring. index.php's own links only ever send "info.php?id=1" or "info.php?id=2" -
// restrict to exactly those known-good values via an allowlist, so legitimate navigation keeps
// working while anything else is rejected.
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
