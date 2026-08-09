<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
This endpoint performs a sensitive write (editing a user's profile) and is
reachable directly, independently of whichever page happens to link to it, so
it must enforce its own admin check regardless of security level.
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

// Bind the caller-supplied values as parameters instead of concatenating them
// into the query text, so nothing in the JSON body can be parsed as SQL.
$new_first_name = isset( $data->first_name ) ? (string) $data->first_name : '';
$new_surname    = isset( $data->surname )    ? (string) $data->surname    : '';
$target_user_id = isset( $data->id )         ? intval( $data->id )       : 0;

$query = "UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?";
$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], $query );
if ( !$stmt ) {
	print json_encode (array ("result" => "fail", "error" => "Unable to update user"));
	exit;
}
mysqli_stmt_bind_param( $stmt, "ssi", $new_first_name, $new_surname, $target_user_id );

// mysqli throws on error by default (PHP >= 8.1), so a bad value (e.g. one
// too long for the column) must be caught here rather than left to crash
// with a raw stack trace.
try {
	$update_ok = mysqli_stmt_execute( $stmt );
} catch ( mysqli_sql_exception $e ) {
	$update_ok = false;
}
mysqli_stmt_close( $stmt );

if ( !$update_ok ) {
	print json_encode (array ("result" => "fail", "error" => "Unable to update user"));
	exit;
}

print json_encode (array ("result" => "ok"));
exit;
?>
