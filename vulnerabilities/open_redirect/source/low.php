<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// Only ever redirect to one of the known, local info pages - never to
	// an arbitrary, attacker-controlled URL.
	$allowedRedirects = [ "info.php?id=1", "info.php?id=2" ];

	if (in_array($_GET['redirect'], $allowedRedirects, true)) {
		header ("location: " . $_GET['redirect']);
		exit;
	} else {
		http_response_code (500);
		?>
		<p>You can only redirect to the info page.</p>
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
