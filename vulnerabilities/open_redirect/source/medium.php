<?php

// Allow-list of the only legitimate redirect targets this module ever needs.
// See ../index.php (which generates these exact links) and source/info.php.
$whitelisted_redirects = array(
	"info.php?id=1",
	"info.php?id=2",
);

if (array_key_exists ("redirect", $_REQUEST) && $_REQUEST['redirect'] != "") {
	$redirect = $_REQUEST['redirect'];

	if (in_array ($redirect, $whitelisted_redirects, true)) {
		header ("location: " . $redirect);
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
