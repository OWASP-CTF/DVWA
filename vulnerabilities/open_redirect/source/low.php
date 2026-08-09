<?php

$target = "";

// The vulnerable request contract accepted the *destination itself* as the
// "redirect" parameter, so no amount of validating that string can ever
// close the hole - the parameter's whole job was choosing where to go.
// Match impossible.php's contract instead: "redirect" names a small fixed
// choice by number, and only this file ever decides what number maps to
// what URL. There is no value the caller can pass that resolves to
// anywhere they chose themselves.
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
	} else {
		http_response_code (500);
		?>
		<p>Unknown redirect target.</p>
		<?php
		exit;
	}
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
