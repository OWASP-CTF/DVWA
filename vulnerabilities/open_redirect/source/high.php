<?php

// Requiring the string 'info.php' to appear anywhere in the value was
// satisfied by 'https://evil.example/info.php'.
//
// The destination is never taken from the request. The parameter selects an
// entry from a server side table, so the set of reachable URLs is fixed.
$targets = array(
	1 => "info.php?id=1",
	2 => "info.php?id=2",
);

$target = "";

if (array_key_exists ("redirect", $_GET) && is_numeric($_GET['redirect'])) {
	$key = intval ($_GET['redirect']);
	if (array_key_exists ($key, $targets)) {
		$target = $targets[$key];
	}
}

if ($target != "") {
	header ("location: " . $target);
	exit;
}

http_response_code (500);
?>
<p>Unknown redirect target.</p>
<?php
exit;
?>
