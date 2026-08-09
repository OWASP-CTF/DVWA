<?php
/*

Only the admin user is allowed to access this page.

The page, the API which returns the user data, and the API which updates it
all check authorisation independently:

* vulnerabilities/authbypass/get_user_data.php
* vulnerabilities/authbypass/change_user_details.php

*/

if (dvwaCurrentUser() != "admin") {
	print "Unauthorised";
	http_response_code(403);
	exit;
}
?>
