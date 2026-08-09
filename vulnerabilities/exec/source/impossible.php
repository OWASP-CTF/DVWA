<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$target = trim( (string) ( $_REQUEST[ 'ip' ] ?? '' ) );
	if( filter_var( $target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) === false ) {
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
	else {
		$target = escapeshellarg( $target );

		// Determine OS and execute the ping command.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			// Windows
			$cmd = shell_exec( 'ping  ' . $target );
		}
		else {
			// *nix
			$cmd = shell_exec( 'ping  -c 4 ' . $target );
		}

		// Feedback for the end user
		$html .= '<pre>' . htmlspecialchars( $cmd, ENT_QUOTES, 'UTF-8' ) . '</pre>';
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
