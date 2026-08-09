<?php
if (!function_exists('dvwaCurrentUser')) {
	http_response_code(403);
	exit('Unauthorised');
}

require __DIR__ . '/impossible.php';
?>
