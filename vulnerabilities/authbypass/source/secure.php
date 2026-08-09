<?php

// Source fragments are normally included by index.php after DVWA has booted.
// Fail closed when this file (or low/medium/high.php) is requested directly,
// instead of calling an unavailable session helper and returning a 500 page.
if (!function_exists('dvwaCurrentUser')) {
	http_response_code(403);
	exit('Unauthorised');
}

if (dvwaCurrentUser() !== 'admin') {
	http_response_code(403);
	exit('Unauthorised');
}

?>
