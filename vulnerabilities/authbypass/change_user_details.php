<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
Only the admin is allowed to change the data. The check is applied here, on the
endpoint itself, not just on the page that calls it, and it is applied at every
security level.
*/

if (!dvwaIsLoggedIn() || dvwaCurrentUser() != "admin") {
	print json_encode (array ("result" => "fail", "error" => "Access denied"));
	exit;
}

if ($_SERVER['REQUEST_METHOD'] != "POST") {
	$result = array (
						"result" => "fail",
						"error" => "Only POST requests are accepted"
					);
	echo json_encode($result);
	exit;
}

try {
	$json = file_get_contents('php://input');
	$data = json_decode($json);
	if (is_null ($data)) {
		$result = array (
							"result" => "fail",
							"error" => 'Invalid format, expecting "{id: {user ID}, first_name: "{first name}", surname: "{surname}"}'

						);
		echo json_encode($result);
		exit;
	}
} catch (Exception $e) {
	$result = array (
						"result" => "fail",
						"error" => 'Invalid format, expecting \"{id: {user ID}, first_name: "{first name}", surname: "{surname}\"}'

					);
	echo json_encode($result);
	exit;
}

// Prepared statement, so nothing supplied in the JSON body can be parsed as SQL.
$first_name = isset( $data->first_name ) ? (string)$data->first_name : '';
$surname    = isset( $data->surname )    ? (string)$data->surname    : '';
$user_id    = isset( $data->id )         ? intval( $data->id )       : 0;

$query = "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?";
$stmt  = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
if (!$stmt) {
	print json_encode (array ("result" => "fail", "error" => "Unable to save"));
	exit;
}
mysqli_stmt_bind_param($stmt, "ssi", $first_name, $surname, $user_id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
	print json_encode (array ("result" => "fail", "error" => "Unable to save"));
	exit;
}

print json_encode (array ("result" => "ok"));
exit;
?>
