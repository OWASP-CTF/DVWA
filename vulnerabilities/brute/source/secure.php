<?php

if (isset($_POST['Login'])) {
	checkToken($_POST['user_token'] ?? '', $_SESSION['session_token'] ?? '', 'index.php');

	$user = trim((string) ($_POST['username'] ?? ''));
	$pass = (string) ($_POST['password'] ?? '');
	$now = time();
	$window = 15 * 60;
	$maximumAttempts = 5;
	mysqli_query($GLOBALS['___mysqli_ston'], 'CREATE TABLE IF NOT EXISTS brute_attempts (username VARCHAR(255) PRIMARY KEY, attempts INT NOT NULL, window_started INT NOT NULL)');
	$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'SELECT attempts, window_started FROM brute_attempts WHERE username = ? LIMIT 1');
	mysqli_stmt_bind_param($stmt, 's', $user);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$attempts = $result ? mysqli_fetch_assoc($result) : null;
	mysqli_stmt_close($stmt);
	if (!$attempts || ($now - (int) $attempts['window_started']) >= $window) {
		$attempts = array('attempts' => 0, 'window_started' => $now);
	}

	if ($attempts['attempts'] >= $maximumAttempts) {
		http_response_code(429);
		$html .= '<pre><br />Too many login attempts. Please try again later.</pre>';
	} elseif ($user === '' || $pass === '') {
		$html .= '<pre><br />Username and/or password incorrect.</pre>';
	} else {
		$passwordHash = md5($pass);
		$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'SELECT avatar FROM users WHERE user = ? AND password = ? LIMIT 1');
		mysqli_stmt_bind_param($stmt, 'ss', $user, $passwordHash);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);

		if ($result && mysqli_num_rows($result) === 1) {
			$row = mysqli_fetch_assoc($result);
			$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'DELETE FROM brute_attempts WHERE username = ?');
			mysqli_stmt_bind_param($stmt, 's', $user);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
			$safeUser = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
			$safeAvatar = htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8');
			$html .= "<p>Welcome to the password protected area {$safeUser}</p>";
			$html .= "<img src=\"{$safeAvatar}\" alt=\"User avatar\" />";
		} else {
			$attempts['attempts']++;
			$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'INSERT INTO brute_attempts (username, attempts, window_started) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE attempts = VALUES(attempts), window_started = VALUES(window_started)');
			mysqli_stmt_bind_param($stmt, 'sii', $user, $attempts['attempts'], $attempts['window_started']);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
			usleep(random_int(250000, 750000));
			$html .= '<pre><br />Username and/or password incorrect.</pre>';
		}
		mysqli_stmt_close($stmt);
	}
}

generateSessionToken();

?>
