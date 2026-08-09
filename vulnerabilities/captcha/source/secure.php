<?php

/*
The CAPTCHA is verified server side and the result of that verification is kept
server side too. The two step flow the module ships with is honoured, but step
two is only ever reachable when this session actually passed the CAPTCHA in
step one, so posting step=2 directly (or adding passed_captcha=true) cannot
skip the check.
*/

if (isset($_POST['Change'])) {
	checkToken($_POST['user_token'] ?? '', $_SESSION['session_token'] ?? '', 'index.php');
	$hide_form = true;

	$step = (string) ($_POST['step'] ?? '1');
	$passNew = (string) ($_POST['password_new'] ?? '');
	$passConfirm = (string) ($_POST['password_conf'] ?? '');
	$passCurrent = (string) ($_POST['password_current'] ?? '');

	$captchaWindow = 300;
	$captchaVerified = isset($_SESSION['captcha_verified_at'])
		&& (time() - (int) $_SESSION['captcha_verified_at']) < $captchaWindow;

	if ($step !== '2') {
		// Step one: the CAPTCHA has to be solved before anything else happens.
		unset($_SESSION['captcha_verified_at']);
		$captchaResponse = (string) ($_POST['g-recaptcha-response'] ?? '');
		$captchaValid = recaptcha_check_answer($_DVWA['recaptcha_private_key'], $captchaResponse);

		if (!$captchaValid) {
			$html .= '<pre><br />The CAPTCHA was incorrect. Please try again.</pre>';
			$hide_form = false;
		} elseif ($passNew === '' || $passNew !== $passConfirm) {
			$html .= '<pre>Either your current password is incorrect or the new passwords did not match.<br />Please try again.</pre>';
			$hide_form = false;
		} else {
			// CAPTCHA passed. Remember that server side and ask for confirmation.
			$_SESSION['captcha_verified_at'] = time();
			$safeNew = htmlspecialchars($passNew, ENT_QUOTES, 'UTF-8');
			$safeConf = htmlspecialchars($passConfirm, ENT_QUOTES, 'UTF-8');
			$safeCurrent = htmlspecialchars($passCurrent, ENT_QUOTES, 'UTF-8');
			$html .= "
				<pre><br />You passed the CAPTCHA! Click the button to confirm your changes.<br /></pre>
				<form action=\"#\" method=\"POST\">
					<input type=\"hidden\" name=\"step\" value=\"2\" />
					<input type=\"hidden\" name=\"password_new\" value=\"{$safeNew}\" />
					<input type=\"hidden\" name=\"password_conf\" value=\"{$safeConf}\" />
					<input type=\"hidden\" name=\"password_current\" value=\"{$safeCurrent}\" />
					<input type=\"submit\" name=\"Change\" value=\"Change\" />
					" . tokenField() . "
				</form>";
		}
	} elseif (!$captchaVerified) {
		// step=2 without this session having passed step one.
		$html .= '<pre><br />The CAPTCHA was incorrect. Please try again.</pre>';
		$hide_form = false;
	} else {
		unset($_SESSION['captcha_verified_at']);

		if ($passNew === '' || $passNew !== $passConfirm) {
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
}

generateSessionToken();

?>
