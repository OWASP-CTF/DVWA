<?php
/*

Hiding the "Authorisation Bypass" menu link from non-admin users in
dvwaHtmlEcho() (dvwa/includes/dvwaPage.inc.php) is not real access control -
it's just security through obscurity. The page itself, and more importantly
the get_user_data.php / change_user_details.php endpoints it calls, must
enforce the restriction server-side regardless of security level.

Only the admin user is allowed to access this page.

*/

if (dvwaCurrentUser() != "admin") {
	print "Unauthorised";
	http_response_code(403);
	exit;
}
?>
