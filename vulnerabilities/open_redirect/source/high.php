<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$redirect = $_GET['redirect'];
	// Require a same-site relative path (no scheme, no //, no backslash) that targets the info page.
	$isRelative = !preg_match ('#^[a-z][a-z0-9+.-]*:#i', $redirect) && substr ($redirect, 0, 2) !== "//" && strpos ($redirect, "\\") === false;
	if ($isRelative && strpos ($redirect, "info.php") !== false) {
		header ("location: " . $redirect);
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
