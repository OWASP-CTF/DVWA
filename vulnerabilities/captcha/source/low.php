<?php

if( isset( $_POST[ 'Change' ] ) && ( $_POST[ 'step' ] == '1' ) ) {
	// Hide the CAPTCHA form
	$hide_form = true;

	// Any earlier clearance is void as soon as stage one is re-entered.
	unset( $_SESSION[ 'captcha_cleared_for' ] );

	// Get input
	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];

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
			// Record the clearance server side, bound to the password that
			// was actually vouched for, so stage two cannot be forged.
			$_SESSION[ 'captcha_cleared_for' ] = hash( 'sha256', $pass_new );

			// Show next stage for the user
			$safe_new  = htmlspecialchars( $pass_new, ENT_QUOTES, 'UTF-8' );
			$safe_conf = htmlspecialchars( $pass_conf, ENT_QUOTES, 'UTF-8' );
			$html .= "
				<pre><br />You passed the CAPTCHA! Click the button to confirm your changes.<br /></pre>
				<form action=\"#\" method=\"POST\">
					<input type=\"hidden\" name=\"step\" value=\"2\" />
					<input type=\"hidden\" name=\"password_new\" value=\"{$safe_new}\" />
					<input type=\"hidden\" name=\"password_conf\" value=\"{$safe_conf}\" />
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

	// Stage two used to trust that stage one had happened. Confirm it against
	// the server-side record instead -- posting straight to step 2 is exactly
	// how the CAPTCHA was skipped.
	$cleared = isset( $_SESSION[ 'captcha_cleared_for' ] )
		&& hash_equals( $_SESSION[ 'captcha_cleared_for' ], hash( 'sha256', $pass_new ) );

	// The clearance is single use whatever happens next.
	unset( $_SESSION[ 'captcha_cleared_for' ] );

	if( !$cleared ) {
		$html     .= "<pre><br />You have not passed the CAPTCHA.</pre>";
		$hide_form = false;
		return;
	}

	// Check to see if both password match
	if( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = md5( stripslashes( $pass_new ) );

		// Update database via a prepared statement
		$current_user = dvwaCurrentUser();
		$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE `users` SET password = ? WHERE user = ?;" );

		if( $stmt ) {
			mysqli_stmt_bind_param( $stmt, "ss", $pass_new, $current_user );
			mysqli_stmt_execute( $stmt );
			mysqli_stmt_close( $stmt );
		}

		// Feedback for the end user
		$html .= "<pre>Password Changed.</pre>";
	}
	else {
		// Issue with the passwords matching
		$html .= "<pre>Passwords did not match.</pre>";
		$hide_form = false;
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
