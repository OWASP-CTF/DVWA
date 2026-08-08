<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// Blocking the "http://" and "https://" prefixes still allowed
	// scheme-relative targets such as "//evil.example". Recognise the one
	// destination this module offers and rebuild the URL from the parsed id.
	if (preg_match ('/^info\.php\?id=([0-9]{1,9})$/', $_GET['redirect'], $matches)) {
		header ("location: info.php?id=" . intval ($matches[1]));
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
