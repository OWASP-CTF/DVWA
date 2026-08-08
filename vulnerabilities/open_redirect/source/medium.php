<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = "";

	// Only ever redirect to the local info page, rebuilt from a numeric id, so
	// the destination can never be chosen by whoever crafted the link.
	if (preg_match ('/^info\.php\?id=([0-9]{1,9})$/', $_GET['redirect'], $matches)) {
		$target = "info.php?id=" . intval ($matches[1]);
	}

	if ($target != "") {
		header ("location: " . $target);
		exit;
	}

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
