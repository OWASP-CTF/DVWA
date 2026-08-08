<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	if (preg_match ('/^info\.php\?id=\d+$/', $_GET['redirect'])) {
		header ("location: " . $_GET['redirect']);
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
