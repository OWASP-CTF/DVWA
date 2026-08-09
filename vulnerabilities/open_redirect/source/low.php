<?php

// The redirect target was previously passed straight into the Location header, allowing an
// attacker to redirect victims to an arbitrary external site. Only allow a small set of known
// internal targets, selected by a numeric id, matching impossible.php's approach.
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
