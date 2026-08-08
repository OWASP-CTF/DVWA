<?php

// A redirect target must never be supplied as a URL. The parameter is now an
// opaque numeric id that the server maps to a destination it chose itself,
// so there is nothing for a caller to point somewhere else.
$target = "";

if (array_key_exists ("redirect", $_GET) && is_numeric ($_GET['redirect'])) {
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

	http_response_code (500);
	?>
	<p>Unknown redirect target.</p>
	<?php
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
