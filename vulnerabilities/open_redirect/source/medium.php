<?php

// Blocking only "http://"/"https://" doesn't stop a protocol-relative URL like "//evil.com",
// which browsers still treat as an absolute redirect to an attacker-controlled host. Only allow
// a small set of known internal targets, selected by a numeric id, matching impossible.php.
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
