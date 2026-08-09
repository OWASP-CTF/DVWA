<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];
	if ($target === 'info.php') {
		header ("location: info.php");
		exit;
	}
	http_response_code(400);
	print "Invalid redirect target.";
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
