<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$target = trim( stripslashes( $_POST[ 'ip' ] ) );

	// The old blacklist missed '|' without a trailing space. A blacklist can
	// always be worked around, so only an exact IPv4 address is accepted.
	if( filter_var( $target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) === false ) {
		// Ops. Let the user know there is a mistake
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
	else {
		// Belt and braces: the validated value is still passed as a single
		// quoted argument so it can never be reinterpreted as shell syntax.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			// Windows
			$cmd = shell_exec( 'ping ' . escapeshellarg( $target ) );
		}
		else {
			// *nix
			$cmd = shell_exec( 'ping -c 4 ' . escapeshellarg( $target ) );
		}

		// Feedback for the end user
		$html .= '<pre>' . htmlspecialchars( (string) $cmd, ENT_QUOTES, 'UTF-8' ) . '</pre>';
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
