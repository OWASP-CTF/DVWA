<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
This endpoint is shared by every security level. Only the admin user is
ever allowed to retrieve user data, regardless of which level rendered the
calling page (or whether the caller went through the page at all) -
enforce it unconditionally rather than branching on the security level.
*/
if (dvwaCurrentUser() != "admin") {
	print json_encode (array ("result" => "fail", "error" => "Access denied"));
	exit;
}

$query  = "SELECT user_id, first_name, last_name FROM users";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

$guestbook = ''; 
$users = array();

/*
Output is HTML-encoded for every level, not just impossible. This endpoint's
JSON is rendered into the DOM with innerHTML (see authbypass.js), and
first_name/surname come straight out of the users table with no guarantee
they are free of HTML metacharacters - matching impossible's encoding here
closes that stored-XSS-via-admin's-browser path uniformly, and it is a
no-op for the stock seed data (gordonb, 1337, pablo, smithy, admin), so the
JSON shape the page consumes is unchanged for the benign path.
*/
while ($row = mysqli_fetch_row($result) ) {
	$user_id = $row[0];
	$first_name = htmlspecialchars( $row[1] );
	$surname = htmlspecialchars( $row[2] );

	$user = array (
					"user_id" => $user_id,
					"first_name" => $first_name,
					"surname" => $surname
				);
	$users[] = $user;
}

print json_encode ($users);
exit;
?>
