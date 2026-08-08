<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	if (!preg_match ('#^[a-z][a-z0-9+.-]*:|^//#i', $_GET['redirect']) && preg_match ('/^info\.php\?id=\d+$/', $_GET['redirect'])) {
		header ("location: " . $_GET['redirect']);
		exit;
	} else {
		http_response_code (500);
		?>
		<p>Unknown redirect target.</p>
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
