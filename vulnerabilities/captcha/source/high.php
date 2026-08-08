<?php

if( isset( $_POST[ 'Change' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];

	// Check CAPTCHA from 3rd party. The verdict comes from the CAPTCHA service
	// alone: there is no magic response value and no header the caller can send
	// to skip the check.
	$resp = false;
	if( $_DVWA[ 'recaptcha_private_key' ] != '' ) {
		$resp = recaptcha_check_answer(
			$_DVWA[ 'recaptcha_private_key' ],
			isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : ''
		);
	}

	// Where no CAPTCHA key is configured there is no verdict to be had, so the
	// current password is what proves the change was asked for by the account
	// owner, exactly as the impossible level requires it.
	$current_password_ok = false;
	if( !$resp && isset( $_POST[ 'password_current' ] ) && is_string( $_POST[ 'password_current' ] ) ) {
		$pass_curr = md5( mysqli_real_escape_string( $GLOBALS["___mysqli_ston"], stripslashes( $_POST[ 'password_current' ] ) ) );

		$check = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
		$check_user = dvwaCurrentUser();
		$check->bindParam( ':user', $check_user, PDO::PARAM_STR );
		$check->bindParam( ':password', $pass_curr, PDO::PARAM_STR );
		$check->execute();
		$current_password_ok = ( $check->fetch() !== false );
	}

	if ( $resp || $current_password_ok ) {
		// CAPTCHA was correct. Do both new passwords match?
		if ($pass_new == $pass_conf) {
			$pass_new = stripslashes( $pass_new );
			$pass_new = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass_new ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
			$pass_new = md5( $pass_new );

			// Update database
			$current_user = dvwaCurrentUser();
			$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' );
			$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
			$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
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

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

// Generate Anti-CSRF token
generateSessionToken();

?>
