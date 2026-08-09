<?php
/*

Nothing to see here for this vulnerability, have a look
instead at the dvwaHtmlEcho function in:

* dvwa/includes/dvwaPage.inc.php

*/

// page is admin-only regardless of level
if (dvwaCurrentUser() != "admin") {
	print "Unauthorised";
	http_response_code(403);
	exit;
}
?>
