<?php

if (array_key_exists ("redirect", $_GET) && is_string ($_GET['redirect']) && $_GET['redirect'] != "") {
	$redirect = parse_url ($_GET['redirect']);

	if ($redirect !== false &&
		!array_key_exists ("scheme", $redirect) &&
		!array_key_exists ("host", $redirect) &&
		!array_key_exists ("user", $redirect) &&
		!array_key_exists ("pass", $redirect) &&
		!array_key_exists ("port", $redirect) &&
		array_key_exists ("path", $redirect) &&
		$redirect['path'] === "info.php") {
		$target = "info.php";
		if (array_key_exists ("query", $redirect)) {
			$target .= "?" . $redirect['query'];
		}
		if (array_key_exists ("fragment", $redirect)) {
			$target .= "#" . $redirect['fragment'];
		}

		header ("location: " . $target);
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
