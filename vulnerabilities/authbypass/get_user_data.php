<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
This endpoint returns every user's profile data and is reachable directly,
independently of whichever page happens to link to it, so it must enforce its
own admin check regardless of security level. The returned fields are also
always HTML-encoded, since they end up rendered back into the calling page.
*/
if (!dvwaIsLoggedIn() || dvwaCurrentUser() != "admin") {
	print json_encode (array ("result" => "fail", "error" => "Access denied"));
	exit;
}

$query  = "SELECT user_id, first_name, last_name FROM users";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

$guestbook = '';
$users = array();

while ($row = mysqli_fetch_row($result) ) {
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
