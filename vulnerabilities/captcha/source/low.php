<?php

if( isset( $_POST[ 'Change' ] ) && ( $_POST[ 'step' ] == '1' ) ) {
	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];
	$pass_curr = $_POST[ 'password_current' ];

	// Check CAPTCHA from 3rd party
	$resp = recaptcha_check_answer(
		$_DVWA[ 'recaptcha_private_key'],
		$_POST['g-recaptcha-response']
	);

	// Did the CAPTCHA fail?
	if( !$resp ) {
		// What happens when the CAPTCHA was entered incorrectly
		$html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>";
		$hide_form = false;
		return;
	}
	else {
		// CAPTCHA was correct. Do both new passwords match?
		if( $pass_new == $pass_conf ) {
			// Show next stage for the user
			$html .= "
				<pre><br />You passed the CAPTCHA! Click the button to confirm your changes.<br /></pre>
				<form action=\"#\" method=\"POST\">
					<input type=\"hidden\" name=\"step\" value=\"2\" />
					<input type=\"hidden\" name=\"password_current\" value=\"{$pass_curr}\" />
					<input type=\"hidden\" name=\"password_new\" value=\"{$pass_new}\" />
					<input type=\"hidden\" name=\"password_conf\" value=\"{$pass_conf}\" />
					<input type=\"submit\" name=\"Change\" value=\"Change\" />
				</form>";
		}
		else {
			// Both new passwords do not match.
			$html     .= "<pre>Both passwords must match.</pre>";
			$hide_form = false;
		}
	}
}

if( isset( $_POST[ 'Change' ] ) && ( $_POST[ 'step' ] == '2' ) ) {
	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];
	$pass_curr = md5( $_POST[ 'password_current' ] );

	// Neither this step, nor the CAPTCHA in step 1, ever proved the requester actually knows the
	// account's current password - anyone who could reach this endpoint (step=2 can be
	// submitted directly, skipping the CAPTCHA in step 1 entirely) could change any logged-in
	// user's password. Verify the current password before allowing the change.
	$currCheck = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT password FROM users WHERE user = ? AND password = ? LIMIT 1;");
	$currUser  = dvwaCurrentUser();
	mysqli_stmt_bind_param($currCheck, 'ss', $currUser, $pass_curr);
	mysqli_stmt_execute($currCheck);
	$currResult = mysqli_stmt_get_result($currCheck);

	// Check to see if both password match and the current password was correct
	if( ( $pass_new == $pass_conf ) && $currResult && mysqli_num_rows( $currResult ) == 1 ) {
		// They do!
		$pass_new = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass_new ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
		$pass_new = md5( $pass_new );

		// Update database
		$insert = "UPDATE `users` SET password = '$pass_new' WHERE user = '" . dvwaCurrentUser() . "';";
		$result = mysqli_query($GLOBALS["___mysqli_ston"],  $insert ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

		// Feedback for the end user
		$html .= "<pre>Password Changed.</pre>";
	}
	else {
		// Issue with the passwords matching or the current password was wrong
		$html .= "<pre>Passwords did not match or current password incorrect.</pre>";
		$hide_form = false;
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
