<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
Only the admin is allowed to retrieve the data. The check is applied here, on
the endpoint itself, not just on the page that calls it, and it is applied at
every security level.
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
