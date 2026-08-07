<?php

// Never reflect a caller supplied string into the Location header.
// The requested target is looked up in a fixed allow list and only the
// hardcoded literal on the right hand side is ever sent to the browser.
$target = "";

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	switch ($_GET['redirect']) {
		case "1":
		case "info.php?id=1":
			$target = "info.php?id=1";
			break;
		case "2":
		case "info.php?id=2":
			$target = "info.php?id=2";
			break;
		case "99":
			$target = "https://digi.ninja";
			break;
	}

	if ($target != "") {
		header ("location: " . $target);
		exit;
	}

	http_response_code (500);
	?>
	<p>You can only redirect to the info page.</p>
	<?php
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
