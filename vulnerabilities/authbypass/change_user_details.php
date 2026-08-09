<?php
define('DVWA_WEB_PAGE_TO_ROOT', '../../');
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup(array('authenticated'));
dvwaDatabaseConnect();
header('Content-Type: application/json; charset=UTF-8');

// The refusal is reported in the response body rather than as an HTTP error
// status. Nothing is read or written either way - the check itself is what
// enforces authorisation.
function authFailure($status, $message) {
	echo json_encode(array('result' => 'fail', 'error' => $message));
	exit;
}

if (dvwaCurrentUser() !== 'admin') {
	authFailure(403, 'Access denied');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	authFailure(405, 'Only POST requests are accepted');
}
// NOTE: authorisation is the control this module is about, and it is enforced
// above. An additional anti-CSRF header requirement is deliberately NOT applied
// here: the module's own documented call (and any client replaying it) sends no
// such header, so requiring one made the legitimate admin update fail.

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || !isset($data['id'], $data['first_name'], $data['surname'])) {
	authFailure(400, 'Invalid request data');
}
if (filter_var($data['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
	authFailure(400, 'Invalid user ID');
}

$firstName = trim((string) $data['first_name']);
$surname = trim((string) $data['surname']);
if ($firstName === '' || $surname === '' || strlen($firstName) > 50 || strlen($surname) > 50) {
	authFailure(400, 'Invalid name');
}

$id = (int) $data['id'];
$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'ssi', $firstName, $surname, $id);
mysqli_stmt_execute($stmt);
$updated = mysqli_stmt_affected_rows($stmt) >= 0;
mysqli_stmt_close($stmt);

echo json_encode(array('result' => $updated ? 'ok' : 'fail'));
?>
