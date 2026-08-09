<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];
	if ($target === 'info.php') {
		header ("location: info.php");
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
