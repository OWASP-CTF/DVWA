<?php

if (array_key_exists ("redirect", $_GET) && is_string ($_GET['redirect']) && $_GET['redirect'] != "") {
	$target = str_replace ('\\', '/', $_GET['redirect']);
	$parts = parse_url ($target);

	if ($parts !== false && !array_key_exists ("scheme", $parts) && !array_key_exists ("host", $parts)) {
		header ("location: " . $target);
		exit;
	}

	http_response_code (500);
	?>
	<p>Absolute URLs not allowed.</p>
	<?php
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
