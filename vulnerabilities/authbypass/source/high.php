<?php
/*

Only the admin user is allowed to access this page.

Have a look at this file for possible vulnerabilities: 

* vulnerabilities/authbypass/change_user_details.php

*/

if (dvwaCurrentUser() != "admin") {
	// Status code first: once anything has been printed the headers are
	// already on their way and http_response_code() is a no-op.
	http_response_code(403);
	print "Unauthorised";
	exit;
}
?>
