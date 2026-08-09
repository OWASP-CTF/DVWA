<?php

if( isset( $_POST[ 'Change' ] ) ) {
	// Check Anti-CSRF token - a token field was already rendered and a fresh token generated at
	// the end of this file, but nothing ever validated the submitted token against it.
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];
	$pass_curr = md5( $_POST[ 'password_current' ] );

	// Check CAPTCHA from 3rd party
	$resp = recaptcha_check_answer(
		$_DVWA[ 'recaptcha_private_key' ],
		$_POST['g-recaptcha-response']
	);

	if (
		$resp ||
		(
			$_POST[ 'g-recaptcha-response' ] == 'hidd3n_valu3'
			&& $_SERVER[ 'HTTP_USER_AGENT' ] == 'reCAPTCHA'
		)
	){
		// CAPTCHA was correct. Verify the current password and check both new passwords match -
		// passing the CAPTCHA alone never proved the requester actually knows the account's
		// current password.
		$currCheck = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT password FROM users WHERE user = ? AND password = ? LIMIT 1;");
		$currUser  = dvwaCurrentUser();
		mysqli_stmt_bind_param($currCheck, 'ss', $currUser, $pass_curr);
		mysqli_stmt_execute($currCheck);
		$currResult = mysqli_stmt_get_result($currCheck);

		if ($pass_new == $pass_conf && $currResult && mysqli_num_rows( $currResult ) == 1) {
			$pass_new = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass_new ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
			$pass_new = md5( $pass_new );

			// Update database
			$insert = "UPDATE `users` SET password = '$pass_new' WHERE user = '" . dvwaCurrentUser() . "' LIMIT 1;";
			$result = mysqli_query($GLOBALS["___mysqli_ston"],  $insert ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

			// Feedback for user
			$html .= "<pre>Password Changed.</pre>";

		} else {
			// Ops. Password mismatch or current password incorrect
			$html     .= "<pre>Both passwords must match and the current password must be correct.</pre>";
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
