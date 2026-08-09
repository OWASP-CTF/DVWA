<?php

if (dvwaCurrentUser() !== 'admin') {
	http_response_code(403);
	exit('Unauthorised');
}

$html = '';

?>
