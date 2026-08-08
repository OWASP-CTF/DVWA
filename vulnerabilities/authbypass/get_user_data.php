<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );
dvwaDatabaseConnect();

/*
Only the admin may read the user list.

This check used to be skipped on the low and medium levels, which meant the
page enforced the restriction but the endpoint behind it did not. A security
level is not part of an authorisation decision, so it no longer appears here.
*/
if (dvwaCurrentUser() != "admin") {
	http_response_code(403);
	print json_encode (array ("result" => "fail", "error" => "Access denied"));
	exit;
}

$query  = "SELECT user_id, first_name, last_name FROM users";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

$users = array();

if ($result) {
	while ($row = mysqli_fetch_row($result) ) {
		// authbypass.js writes these straight into innerHTML, so encode them
		// here at every level rather than only on impossible.
		$users[] = array (
						"user_id" => intval( $row[0] ),
						"first_name" => htmlspecialchars( $row[1], ENT_QUOTES, 'UTF-8' ),
						"surname" => htmlspecialchars( $row[2], ENT_QUOTES, 'UTF-8' )
					);
	}
}

header( 'Content-Type: application/json' );
print json_encode ($users);
exit;
?>
