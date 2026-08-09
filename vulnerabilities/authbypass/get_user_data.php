<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup(array('authenticated'));
header('Content-Type: application/json; charset=UTF-8');

dvwaDatabaseConnect();

/*
Only the admin is allowed to retrieve the data, at every security level. The
refusal is reported in the response body rather than as an HTTP error status -
no user data is returned either way.
*/
if (dvwaCurrentUser() !== 'admin') {
	print json_encode (array ("result" => "fail", "error" => "Access denied"));
	exit;
}

$query  = "SELECT user_id, first_name, last_name FROM users";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

$guestbook = ''; 
$users = array();

while ($row = mysqli_fetch_row($result) ) { 
	$user_id = (int) $row[0];
	$first_name = $row[1];
	$surname = $row[2];

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
