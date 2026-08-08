<?php

$captcha_step = ( isset( $_POST[ 'step' ] ) && is_string( $_POST[ 'step' ] ) ) ? $_POST[ 'step' ] : '';

if( isset( $_POST[ 'Change' ] ) && ( $captcha_step === '1' ) ) {
	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = isset( $_POST[ 'password_new' ] ) && is_string( $_POST[ 'password_new' ] ) ? $_POST[ 'password_new' ] : '';
	$pass_conf = isset( $_POST[ 'password_conf' ] ) && is_string( $_POST[ 'password_conf' ] ) ? $_POST[ 'password_conf' ] : '';

	// Starting the flow again voids any CAPTCHA this session passed before
	unset( $_SESSION[ 'captcha_passed' ], $_SESSION[ 'captcha_password_hash' ], $_SESSION[ 'captcha_passed_at' ] );

	// Check CAPTCHA from 3rd party
	$resp = recaptcha_check_answer(
		$_DVWA[ 'recaptcha_private_key' ],
		isset( $_POST['g-recaptcha-response'] ) && is_string( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : ''
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
		if( hash_equals( $pass_new, $pass_conf ) ) {
			// Record - server side only - that this session passed the CAPTCHA.
			// A hidden form field would be under the attacker's control.
			$_SESSION[ 'captcha_passed' ] = true;
			$_SESSION[ 'captcha_password_hash' ] = hash( 'sha256', $pass_new );
			$_SESSION[ 'captcha_passed_at' ] = time();
			$pass_new_html = htmlspecialchars( $pass_new, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8' );
			$pass_conf_html = htmlspecialchars( $pass_conf, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8' );

			// Show next stage for the user
			$html .= "
				<pre><br />You passed the CAPTCHA! Click the button to confirm your changes.<br /></pre>
				<form action=\"#\" method=\"POST\">
					<input type=\"hidden\" name=\"step\" value=\"2\" />
					<input type=\"hidden\" name=\"password_new\" value=\"{$pass_new_html}\" />
					<input type=\"hidden\" name=\"password_conf\" value=\"{$pass_conf_html}\" />
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

if( isset( $_POST[ 'Change' ] ) && ( $captcha_step === '2' ) ) {
	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = isset( $_POST[ 'password_new' ] ) && is_string( $_POST[ 'password_new' ] ) ? $_POST[ 'password_new' ] : '';
	$pass_conf = isset( $_POST[ 'password_conf' ] ) && is_string( $_POST[ 'password_conf' ] ) ? $_POST[ 'password_conf' ] : '';

	// Check to see if they did stage 1 - only the server side session state is
	// trusted here, never a value that came back with the request.
	$pending_is_valid = isset( $_SESSION[ 'captcha_passed' ], $_SESSION[ 'captcha_password_hash' ], $_SESSION[ 'captcha_passed_at' ] ) &&
		$_SESSION[ 'captcha_passed' ] === true &&
		is_string( $_SESSION[ 'captcha_password_hash' ] ) &&
		is_int( $_SESSION[ 'captcha_passed_at' ] ) &&
		( time() - $_SESSION[ 'captcha_passed_at' ] ) <= 300 &&
		hash_equals( $_SESSION[ 'captcha_password_hash' ], hash( 'sha256', $pass_new ) );

	if( !$pending_is_valid ) {
		unset( $_SESSION[ 'captcha_passed' ], $_SESSION[ 'captcha_password_hash' ], $_SESSION[ 'captcha_passed_at' ] );
		$html     .= "<pre><br />You have not passed the CAPTCHA.</pre>";
		$hide_form = false;
		return;
	}

	// A passed CAPTCHA is good for one password change only
	unset( $_SESSION[ 'captcha_passed' ], $_SESSION[ 'captcha_password_hash' ], $_SESSION[ 'captcha_passed_at' ] );

	// Check to see if both password match
	if( hash_equals( $pass_new, $pass_conf ) ) {
		// They do!
		$pass_new = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass_new ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
		$pass_new = md5( $pass_new );

		// Update database
		$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' );
		$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
		$data->bindValue( ':user', dvwaCurrentUser(), PDO::PARAM_STR );
		$data->execute();

		// Feedback for the end user
		$html .= "<pre>Password Changed.</pre>";
	}
	else {
		// Issue with the passwords matching
		$html .= "<pre>Passwords did not match.</pre>";
		$hide_form = false;
	}
}

?>
