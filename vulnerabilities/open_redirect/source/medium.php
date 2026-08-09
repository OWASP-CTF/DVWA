<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	if (preg_match ("/http:\/\/|https:\/\//i", $_GET['redirect'])) {
		http_response_code (500);
		?>
		<p>Absolute URLs not allowed.</p>
		<?php
		exit;
	}

	// Only the two known quote pages are reachable, and each is a literal destination
	if ($_GET['redirect'] == "info.php?id=1") {
		header ("location: info.php?id=1");
		exit;
	}

	if ($_GET['redirect'] == "info.php?id=2") {
		header ("location: info.php?id=2");
		exit;
	}

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
