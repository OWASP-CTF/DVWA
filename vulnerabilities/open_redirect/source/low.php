<?php

$target = "";

// The caller must not be able to name an arbitrary destination - "redirect"
// is only ever allowed to select a quote on this module's own info page, by
// numeric id. Validate the whole string against that exact shape and rebuild
// the header value from the captured digits, so nothing the caller supplies
// is ever written into the Location header verbatim.
if (array_key_exists ("redirect", $_GET) &&
	preg_match ('/^info\.php\?id=([0-9]{1,9})$/', $_GET['redirect'], $matches)) {
	$target = "info.php?id=" . intval ($matches[1]);
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
?>
