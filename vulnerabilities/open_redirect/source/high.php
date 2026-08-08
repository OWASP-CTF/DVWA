<?php

// A redirect target must never be supplied as a URL. The parameter is now an
// opaque numeric id that the server maps to a destination it chose itself,
// so there is nothing for a caller to point somewhere else.
$target = "";

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] !== "") {
	// Accept either the bare id or the "info.php?id=N" form the module has
	// always linked to, and resolve both to an id before doing anything else.
	$requested = $_GET['redirect'];
	if (preg_match ('/^info\.php\?id=([0-9]{1,9})$/', $requested, $matches)) {
		$requested = $matches[1];
	}

	if (!is_numeric ($requested)) {
		http_response_code (500);
		?>
		<p>Unknown redirect target.</p>
		<?php
		exit;
	}

	switch (intval ($requested)) {
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
