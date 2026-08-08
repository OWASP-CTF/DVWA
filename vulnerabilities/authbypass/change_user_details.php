<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
This endpoint is shared by every security level. Only the admin user is
ever allowed to change user data, regardless of which level rendered the
calling page (or whether the caller went through the page at all) -
enforce it unconditionally rather than branching on the security level.
*/

if (dvwaCurrentUser() != "admin") {
	http_response_code(403);
	header('Content-Type: application/json; charset=UTF-8');
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

/*
The query below used to be built by concatenating $data->id / first_name /
surname straight into the SQL string. That is a SQL injection regardless of
who is allowed to call this endpoint - the admin-only check above stops the
authorisation-bypass exploit this module is about, but it does nothing to
stop an authenticated admin request (or any request that gets past a future
change to that check) from breaking out of the string. Parameterise it with
a prepared statement, matching the pattern already used elsewhere in this
codebase (e.g. vulnerabilities/bac/source/low.php).
*/
if (!isset($data->id) || !is_numeric($data->id)
	|| !isset($data->first_name) || !is_scalar($data->first_name)
	|| !isset($data->surname) || !is_scalar($data->surname)) {
	$result = array (
						"result" => "fail",
						"error" => 'Invalid format, expecting "{id: {user ID}, first_name: "{first name}", surname: "{surname}"}'
					);
	echo json_encode($result);
	exit;
}

$id         = (int) $data->id;
$first_name = (string) $data->first_name;
$surname    = (string) $data->surname;

$query = "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?";
$stmt  = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
if ($stmt) {
	mysqli_stmt_bind_param($stmt, "ssi", $first_name, $surname, $id);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
}

print json_encode (array ("result" => "ok"));
exit;
?>
