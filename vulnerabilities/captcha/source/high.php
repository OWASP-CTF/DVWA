<?php

if( isset( $_POST[ 'Change' ] ) ) {
	// The high-level form already renders this token. Validate it before the
	// state-changing operation rather than treating it as decoration.
	checkToken( $_POST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? '', 'index.php' );

	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];

	// Check CAPTCHA from 3rd party
	$resp = false;
	if( $_DVWA[ 'recaptcha_private_key' ] != '' ) {
		$resp = recaptcha_check_answer(
			$_DVWA[ 'recaptcha_private_key' ],
			$_POST['g-recaptcha-response'] ?? ''
		);
	}

	// A default deployment has no reCAPTCHA key. Preserve a legitimate,
	// fail-secure path by requiring knowledge of the current password instead
	// of accepting a caller-controlled magic response or request header.
	$current_password_ok = false;
	if( !$resp && isset( $_POST[ 'password_current' ] ) && is_string( $_POST[ 'password_current' ] ) ) {
		$pass_current = md5( stripslashes( $_POST[ 'password_current' ] ) );
		$check_user = dvwaCurrentUser();
		$check = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
		$check->bindParam( ':user', $check_user, PDO::PARAM_STR );
		$check->bindParam( ':password', $pass_current, PDO::PARAM_STR );
		$check->execute();
		$current_password_ok = ( $check->fetch() !== false );
	}

	// Accept either a real third-party verdict or the explicit current-password
	// fallback above. Neither decision trusts a magic response value, a header,
	// or client-carried "passed" state.
	if( $resp || $current_password_ok ) {
		// CAPTCHA was correct. Do both new passwords match?
		if ($pass_new == $pass_conf) {
			$pass_new = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass_new ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
			$pass_new = md5( $pass_new );

			// Update database
			$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user) LIMIT 1;' );
			$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
			$data->bindValue( ':user', dvwaCurrentUser(), PDO::PARAM_STR );
			$data->execute();

			// Feedback for user
			$html .= "<pre>Password Changed.</pre>";

		} else {
			// Ops. Password mismatch
			$html     .= "<pre>Both passwords must match.</pre>";
			$hide_form = false;
		}

	} else {
		// What happens when the CAPTCHA was entered incorrectly
		$html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>";
		$hide_form = false;
		return;
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
