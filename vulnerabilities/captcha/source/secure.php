<?php

if (isset($_POST['Change'])) {
	checkToken($_POST['user_token'] ?? '', $_SESSION['session_token'] ?? '', 'index.php');
	$hide_form = true;

	$passNew = (string) ($_POST['password_new'] ?? '');
	$passConfirm = (string) ($_POST['password_conf'] ?? '');
	$passCurrent = (string) ($_POST['password_current'] ?? '');
	$captchaResponse = (string) ($_POST['g-recaptcha-response'] ?? '');
	$captchaValid = recaptcha_check_answer($_DVWA['recaptcha_private_key'], $captchaResponse);

	if (!$captchaValid) {
		$html .= '<pre><br />The CAPTCHA was incorrect. Please try again.</pre>';
		$hide_form = false;
	} elseif ($passNew === '' || $passNew !== $passConfirm) {
		$html .= '<pre>Either your current password is incorrect or the new passwords did not match.<br />Please try again.</pre>';
		$hide_form = false;
	} else {
		$currentHash = md5($passCurrent);
		$newHash = md5($passNew);
		$user = dvwaCurrentUser();
		$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'SELECT user_id FROM users WHERE user = ? AND password = ? LIMIT 1');
		mysqli_stmt_bind_param($stmt, 'ss', $user, $currentHash);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$currentPasswordValid = $result && mysqli_num_rows($result) === 1;
		mysqli_stmt_close($stmt);

		if ($currentPasswordValid) {
			$stmt = mysqli_prepare($GLOBALS['___mysqli_ston'], 'UPDATE users SET password = ? WHERE user = ?');
			mysqli_stmt_bind_param($stmt, 'ss', $newHash, $user);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
			$html .= '<pre>Password Changed.</pre>';
		} else {
			$html .= '<pre>Either your current password is incorrect or the new passwords did not match.<br />Please try again.</pre>';
			$hide_form = false;
		}
	}
}

generateSessionToken();

?>
