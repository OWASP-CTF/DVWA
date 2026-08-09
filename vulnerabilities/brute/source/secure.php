<?php

if (isset($_POST['Login'])) {
	checkToken($_POST['user_token'] ?? '', $_SESSION['session_token'] ?? '', 'index.php');

	$user = trim((string) ($_POST['username'] ?? ''));
	$pass = (string) ($_POST['password'] ?? '');
	$attemptKey = hash('sha256', strtolower($user));
	$now = time();
	$window = 15 * 60;
	$maximumAttempts = 5;
	$attempts = $_SESSION['brute_attempts'][$attemptKey] ?? array('count' => 0, 'since' => $now);

	if (($now - $attempts['since']) >= $window) {
		$attempts = array('count' => 0, 'since' => $now);
	}

	if ($attempts['count'] >= $maximumAttempts) {
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
			unset($_SESSION['brute_attempts'][$attemptKey]);
			$safeUser = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
			$safeAvatar = htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8');
			$html .= "<p>Welcome to the password protected area {$safeUser}</p>";
			$html .= "<img src=\"{$safeAvatar}\" alt=\"User avatar\" />";
		} else {
			$attempts['count']++;
			$_SESSION['brute_attempts'][$attemptKey] = $attempts;
			usleep(random_int(250000, 750000));
			$html .= '<pre><br />Username and/or password incorrect.</pre>';
		}
		mysqli_stmt_close($stmt);
	}
}

generateSessionToken();

?>
