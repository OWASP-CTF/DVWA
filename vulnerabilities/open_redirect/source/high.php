<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
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
