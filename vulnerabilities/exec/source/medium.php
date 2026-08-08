<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );
	// Get input
	$target = trim( $_REQUEST[ 'ip' ] );

	// Only accept a well-formed IPv4 address. A blacklist of individual characters
	// (the old '&&'/';' substitution) is trivially bypassed, so validate the whole
	// value instead of trying to strip "bad" characters out of it.
	if( filter_var( $target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false ) {
		// Determine OS and execute the ping command.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			// Windows
			$cmd = shell_exec( 'ping  ' . escapeshellarg( $target ) );
		}
		else {
			// *nix
			$cmd = shell_exec( 'ping  -c 4 ' . escapeshellarg( $target ) );
		}

		// Feedback for the end user
		$html .= "<pre>{$cmd}</pre>";
	}
	else {
		// Ops. Let the user name theres a mistake
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

generateSessionToken();

?>
