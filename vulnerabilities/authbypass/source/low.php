<?php
/*

Only the admin user is allowed to access this page. At the low level the
only "protection" was hiding the menu link in the UI, so any authenticated
user could still reach this page directly. Enforce it server-side here too,
matching the medium/high/impossible levels.

*/

if (dvwaCurrentUser() != "admin") {
	http_response_code(403);
	print "Unauthorised";
	exit;
}
?>
