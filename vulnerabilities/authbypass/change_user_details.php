<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
Only the admin is allowed to change user details. The authorisation check is
enforced server side on every request, at every security level - the AJAX
endpoint is reachable directly, so it cannot rely on the index page's check.
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

// User controlled values are bound as parameters, never concatenated into SQL.
if ( !isset( $data->first_name ) || !isset( $data->surname ) || !isset( $data->id ) || !is_numeric( $data->id ) ) {
	print json_encode (array ("result" => "fail", "error" => 'Invalid format, expecting "{id: {user ID}, first_name: "{first name}", surname: "{surname}"}'));
	exit;
}

$first_name = (string) $data->first_name;
$surname    = (string) $data->surname;
$user_id    = (int) $data->id;

$query = "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?";
$stmt  = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
if ($stmt === false) {
	print json_encode (array ("result" => "fail", "error" => "Database error"));
	exit;
}
mysqli_stmt_bind_param($stmt, "ssi", $first_name, $surname, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

print json_encode (array ("result" => "ok"));
exit;
?>
