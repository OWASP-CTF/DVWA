<?php
/*

Only the admin user is allowed to access this page

*/

if (dvwaCurrentUser() != "admin") {
	// Status code first: once anything has been printed the headers are
	// already on their way and http_response_code() is a no-op.
	http_response_code(403);
	print "Unauthorised";
	exit;
}
?>
