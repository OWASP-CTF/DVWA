<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
Only the admin is allowed to change user data, regardless of security level.
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

if (!is_numeric($data->id)) {
	print json_encode (array ("result" => "fail", "error" => "Invalid user ID"));
	exit;
}
$id = intval($data->id);

$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'ssi', $data->first_name, $data->surname, $id);
mysqli_stmt_execute($stmt) or die( '<pre>' . mysqli_error($GLOBALS["___mysqli_ston"]) . '</pre>' );
mysqli_stmt_close($stmt);

print json_encode (array ("result" => "ok"));
exit;
?>
