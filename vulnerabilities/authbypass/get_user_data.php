<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
Only the admin is allowed to retrieve the data.

This endpoint is reachable directly, so gating it on the current security
level meant that at low and medium any authenticated user could pull the
whole user table straight out of it, bypassing the admin-only page in front.
*/
if (dvwaCurrentUser() != "admin") {
	print json_encode (array ("result" => "fail", "error" => "Access denied"));
	exit;
}

$query  = "SELECT user_id, first_name, last_name FROM users";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

$guestbook = ''; 
$users = array();

while ($row = mysqli_fetch_row($result) ) { 
	// Encode regardless of level -- the caller renders these values.
	$user_id = $row[0];
	$first_name = htmlspecialchars( $row[1], ENT_QUOTES, 'UTF-8' );
	$surname = htmlspecialchars( $row[2], ENT_QUOTES, 'UTF-8' );

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
