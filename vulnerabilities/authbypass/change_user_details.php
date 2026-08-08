<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );
dvwaDatabaseConnect();

/*
Only the admin may edit user details.

This check used to run on the impossible level only, so on low, medium and
high any authenticated user could rewrite any other user's record straight
through this endpoint.
*/
if (dvwaCurrentUser() != "admin") {
	http_response_code(403);
	print json_encode (array ("result" => "fail", "error" => "Access denied"));
	exit;
}

header( 'Content-Type: application/json' );

if ($_SERVER['REQUEST_METHOD'] != "POST") {
	http_response_code(405);
	echo json_encode (array (
						"result" => "fail",
						"error" => "Only POST requests are accepted"
					));
	exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json);

if (is_null ($data) || !isset ($data->id) || !isset ($data->first_name) || !isset ($data->surname)) {
	http_response_code(400);
	echo json_encode (array (
						"result" => "fail",
						"error" => 'Invalid format, expecting {id: {user ID}, first_name: "{first name}", surname: "{surname}"}'
					));
	exit;
}

if (!is_numeric ($data->id)) {
	http_response_code(400);
	echo json_encode (array ("result" => "fail", "error" => "id must be a number"));
	exit;
}

$id         = intval ($data->id);
$first_name = substr ((string) $data->first_name, 0, 15);
$surname    = substr ((string) $data->surname, 0, 15);

// Bound parameters: the values are sent separately from the statement, so the
// concatenated UPDATE that used to sit here can no longer be steered.
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?");
if (!$stmt) {
	http_response_code(500);
	error_log ("authbypass: unable to prepare update: " . mysqli_error($GLOBALS["___mysqli_ston"]));
	echo json_encode (array ("result" => "fail", "error" => "Unable to save"));
	exit;
}

mysqli_stmt_bind_param($stmt, "ssi", $first_name, $surname, $id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
	http_response_code(500);
	error_log ("authbypass: update failed for user_id {$id}");
	echo json_encode (array ("result" => "fail", "error" => "Unable to save"));
	exit;
}

print json_encode (array ("result" => "ok"));
exit;
?>
