<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
Only the admin is allowed to change user details.

This endpoint is reachable directly, so gating it on the current security
level meant that at low, medium and high any authenticated user could
rewrite any other user's details by POSTing to it.
*/

if (dvwaCurrentUser() != "admin") {
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

// Every one of these values came out of the request body, so they are bound
// as parameters rather than pasted into the statement.
if (!isset ($data->id) || !is_numeric ($data->id) || !isset ($data->first_name) || !isset ($data->surname)) {
	print json_encode (array ("result" => "fail", "error" => 'Invalid format, expecting "{id: {user ID}, first_name: "{first name}", surname: "{surname}"}'));
	exit;
}

$user_id    = intval ($data->id);
$first_name = (string) $data->first_name;
$surname    = (string) $data->surname;

$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?");

if (!$stmt) {
	print json_encode (array ("result" => "fail", "error" => "Unable to update user"));
	exit;
}

mysqli_stmt_bind_param($stmt, "ssi", $first_name, $surname, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

print json_encode (array ("result" => "ok"));
exit;
?>
