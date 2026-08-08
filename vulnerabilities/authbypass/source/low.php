<?php
/*

Only the admin user is allowed to access this page.

This level used to rely purely on the menu in dvwa/includes/dvwaPage.inc.php
hiding the link from non-admins. Hiding a link is not access control: the URL
was still reachable by typing it. The check below is the actual authorisation
decision, made server side on every request.

*/

if (dvwaCurrentUser() != "admin") {
	// Status code first: once anything has been printed the headers are
	// already on their way and http_response_code() is a no-op.
	http_response_code(403);
	print "Unauthorised";
	exit;
}
?>
