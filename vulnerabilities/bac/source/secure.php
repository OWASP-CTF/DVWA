<?php

/*
Access control is the control this module teaches: a user may only view their
own profile, and the identity used for that decision comes from the session
user looked up in the database - never from a client supplied user_id or
user_role cookie.

Everything else the module ships with is left alone. In particular $html is
appended to rather than reset (resetting it destroyed the Access Log panel that
index.php builds above this include), and access attempts are logged again so
that panel can populate.
*/

if (!function_exists('bacLogAccessAttempt')) {
	function bacLogAccessAttempt($user_id, $target_id, $action) {
		$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'INSERT INTO bac_log (user_id, target_id, ip_address, action) VALUES (?, ?, ?, ?)');
		if ($stmt) {
			$user_id = (int) $user_id;
			$target_id = (int) $target_id;
			mysqli_stmt_bind_param($stmt, 'iiss', $user_id, $target_id, $ip, $action);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		}
	}
}

$currentUser = dvwaCurrentUser();
$currentUserId = 0;
$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'SELECT user_id FROM users WHERE user = ? LIMIT 1');
if ($stmt) {
	mysqli_stmt_bind_param($stmt, 's', $currentUser);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$currentRow = $result ? mysqli_fetch_assoc($result) : null;
	$currentUserId = $currentRow ? (int) $currentRow['user_id'] : 0;
	mysqli_stmt_close($stmt);
}

if (isset($_GET['user_id'])) {
	$requestedId = filter_var($_GET['user_id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

	if ($requestedId === false) {
		$html .= '<p>Invalid user ID format. Please enter a number.</p>';
	} elseif ((int) $requestedId !== $currentUserId) {
		http_response_code(403);
		$html .= '<p>Access denied. You can only view your own profile.</p>';
		bacLogAccessAttempt($currentUserId, $requestedId, 'unauthorized_access');
	} else {
		$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ? LIMIT 1');
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, 'i', $currentUserId);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
			if ($result && ($row = mysqli_fetch_assoc($result))) {
				$id = htmlspecialchars((string) $row['user_id'], ENT_QUOTES, 'UTF-8');
				$firstName = htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8');
				$lastName = htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8');
				$avatar = htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8');
				$html .= "<div class=\"profile-info\"><h3>User Profile</h3><p>User ID: {$id}</p><p>Name: {$firstName} {$lastName}</p><p>Avatar: {$avatar}</p></div>";
				bacLogAccessAttempt($currentUserId, $requestedId, 'view_profile_success');
			}
			mysqli_stmt_close($stmt);
		}
	}
}

/*
The module shows the caller's role. The value is read from the database for the
session user - the user_role cookie the base module echoed here is never
consulted, so the banner is informational only and cannot be spoofed.
*/
$currentRole = 'regular_user';
$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'SELECT role FROM users WHERE user_id = ? LIMIT 1');
if ($stmt) {
	mysqli_stmt_bind_param($stmt, 'i', $currentUserId);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	if ($result && ($roleRow = mysqli_fetch_assoc($result)) && !empty($roleRow['role'])) {
		$currentRole = $roleRow['role'];
	}
	mysqli_stmt_close($stmt);
}
$html .= "<div class='info-banner'>Current Role: " . htmlspecialchars((string) $currentRole, ENT_QUOTES, 'UTF-8') . "</div>";

?>
