<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	if ($_GET['redirect'] == "info.php?id=1" || $_GET['redirect'] == "info.php?id=2") {
		header ("location: " . $_GET['redirect']);
	} else {
		http_response_code (500);
	}
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
