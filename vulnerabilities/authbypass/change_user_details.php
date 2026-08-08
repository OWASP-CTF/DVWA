<?php
define('DVWA_WEB_PAGE_TO_ROOT', '../../');
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup(array('authenticated'));
dvwaDatabaseConnect();
header('Content-Type: application/json; charset=UTF-8');

function authFailure($status, $message) {
	http_response_code($status);
	echo json_encode(array('result' => 'fail', 'error' => $message));
	exit;
}

if (dvwaCurrentUser() !== 'admin') {
	authFailure(403, 'Access denied');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	authFailure(405, 'Only POST requests are accepted');
}
$requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($requestToken) || !hash_equals($_SESSION['session_token'] ?? '', $requestToken)) {
	authFailure(403, 'Invalid CSRF token');
}

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
