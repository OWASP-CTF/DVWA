<?php

// strpos() only checked that "info.php" appears *anywhere* in the value - a payload like
// "//evil.com/?info.php" or "https://evil.com/#info.php" still satisfies the check while
// redirecting off-site, since the Location header is taken from the whole string, not just the
// matched substring. Only allow a small set of known internal targets, selected by a numeric
// id, matching impossible.php.
$target = "";

if (array_key_exists ("redirect", $_GET) && is_numeric($_GET['redirect'])) {
	switch (intval ($_GET['redirect'])) {
		case 1:
			$target = "info.php?id=1";
			break;
		case 2:
			$target = "info.php?id=2";
			break;
		case 99:
			$target = "https://digi.ninja";
			break;
	}
	if ($target != "") {
		header ("location: " . $target);
		exit;
	}
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
