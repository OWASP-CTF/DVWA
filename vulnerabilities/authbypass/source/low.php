<?php
/*

Only the admin user is allowed to access this page. Hiding the menu entry in
dvwaHtmlEcho (dvwa/includes/dvwaPage.inc.php) is not an access control, so the
check is enforced server side here as well.

*/

if (dvwaCurrentUser() != "admin") {
	print "Unauthorised";
	http_response_code(403);
	exit;
}
?>
