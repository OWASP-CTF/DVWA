<?php
/*

Only the admin user is allowed to access this page.

Hiding the menu entry in dvwaHtmlEcho() (dvwa/includes/dvwaPage.inc.php)
hid the link but not the page: anyone who typed the URL got straight in.
The page now enforces the same check it advertises.

*/

if (dvwaCurrentUser() != "admin") {
	print "Unauthorised";
	http_response_code(403);
	exit;
}
?>
