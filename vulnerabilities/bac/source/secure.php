<?php

$html = '';
$currentUser = dvwaCurrentUser();
$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'SELECT user_id FROM users WHERE user = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $currentUser);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$currentRow = $result ? mysqli_fetch_assoc($result) : null;
$currentUserId = $currentRow ? (int) $currentRow['user_id'] : 0;
mysqli_stmt_close($stmt);
$defaultProfileId = htmlspecialchars((string) $currentUserId, ENT_QUOTES, 'UTF-8');

if (isset($_GET['action'], $_GET['user_id'])) {
	$requestedId = filter_var($_GET['user_id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
	if ($requestedId === false) {
		$html .= '<p>Invalid user ID format.</p>';
	} elseif ((int) $requestedId !== $currentUserId) {
		http_response_code(403);
		$html .= '<p>Access denied. You can only view your own profile.</p>';
	} else {
		$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ? LIMIT 1');
		mysqli_stmt_bind_param($stmt, 'i', $currentUserId);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		if ($result && ($row = mysqli_fetch_assoc($result))) {
			$id = htmlspecialchars((string) $row['user_id'], ENT_QUOTES, 'UTF-8');
			$firstName = htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8');
			$lastName = htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8');
			$avatar = htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8');
			$html .= "<div class=\"profile-info\"><h3>User Profile</h3><p>User ID: {$id}</p><p>Name: {$firstName} {$lastName}</p><p>Avatar: {$avatar}</p></div>";
		}
		mysqli_stmt_close($stmt);
	}
}

?>
