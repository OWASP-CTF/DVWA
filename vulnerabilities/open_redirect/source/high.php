<?php

if (array_key_exists ("redirect", $_GET) && is_string ($_GET['redirect']) && $_GET['redirect'] != "") {
	$targets = array (
		"info.php?id=1" => "info.php?id=1",
		"info.php?id=2" => "info.php?id=2"
	);

	if (array_key_exists ($_GET['redirect'], $targets)) {
		header ("location: " . $targets[$_GET['redirect']]);
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
