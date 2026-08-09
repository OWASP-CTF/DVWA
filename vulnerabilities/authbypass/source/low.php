<?php
/*

The "Authorisation Bypass" menu link was only ever *rendered* for
dvwaCurrentUser() == "admin" in dvwaHtmlEcho() - that's UI hiding, not
access control. Any authenticated non-admin user who navigated
directly to this page got the full page. Enforce the check
server-side instead, matching impossible.php.

*/

if (dvwaCurrentUser() != "admin") {
	print "Unauthorised";
	http_response_code(403);
	exit;
}
?>
