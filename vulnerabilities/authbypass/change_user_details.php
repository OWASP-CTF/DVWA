<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
Only the admin is allowed to update the data. State changing calls need the
same authorisation check as the calls which read the data, applied on every
request, at every security level, based on the server side session.
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
	if (is_null ($data) || !is_object ($data) || !isset ($data->id) || !isset ($data->first_name) || !isset ($data->surname) || !is_scalar ($data->first_name) || !is_scalar ($data->surname) || !is_numeric ($data->id)) {
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

$first_name = (string) $data->first_name;
$surname    = (string) $data->surname;
$user_id    = (int) $data->id;

$query = "UPDATE users SET first_name = ?, last_name = ? where user_id = ?";
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );
mysqli_stmt_bind_param($stmt, "ssi", $first_name, $surname, $user_id);
$result = mysqli_stmt_execute($stmt) or die( '<pre>' . mysqli_stmt_error($stmt) . '</pre>' );
mysqli_stmt_close($stmt);

print json_encode (array ("result" => "ok"));
exit;
?>
