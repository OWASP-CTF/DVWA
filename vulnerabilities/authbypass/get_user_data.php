<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
On low, high and impossible, only the admin is allowed to retrieve the data.
(This whole page is meant to be an admin-only panel - source/medium.php enforces
that server-side, but at low this endpoint had no check of its own. The
"Authorisation Bypass" link being hidden from non-admins in dvwaHtmlEcho() only
removes a menu item; it does not stop a logged-in low-level user from calling
this endpoint directly and dumping every user's data.)
*/
if ((dvwaSecurityLevelGet() == "low" || dvwaSecurityLevelGet() == "high" || dvwaSecurityLevelGet() == "impossible") && dvwaCurrentUser() != "admin") {
	print json_encode (array ("result" => "fail", "error" => "Access denied"));
	exit;
}

$query  = "SELECT user_id, first_name, last_name FROM users";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

$guestbook = ''; 
$users = array();

while ($row = mysqli_fetch_row($result) ) { 
	if( dvwaSecurityLevelGet() == 'impossible' ) { 
		$user_id = $row[0];
		$first_name = htmlspecialchars( $row[1] );
		$surname = htmlspecialchars( $row[2] );
	} else {
		$user_id = $row[0];
		$first_name = $row[1];
		$surname = $row[2];
	}   

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
