<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim($_REQUEST[ 'ip' ]);

	// A character blacklist is trivially bypassed. Validate the input shape
	// instead - only a plain dotted-quad IP is ever handed to the shell.
	$octet = explode( ".", $target );

	if( ( sizeof( $octet ) == 4 ) && ( is_numeric( $octet[0] ) ) && ( is_numeric( $octet[1] ) ) && ( is_numeric( $octet[2] ) ) && ( is_numeric( $octet[3] ) ) ) {
		$target = $octet[0] . '.' . $octet[1] . '.' . $octet[2] . '.' . $octet[3];

		// Determine OS and execute the ping command. The target is passed as a
		// single escaped shell argument, on top of the octet validation above,
		// so it can never be interpreted as shell syntax.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			// Windows
			$cmd = shell_exec( 'ping  ' . escapeshellarg( $target ) );
		}
		else {
			// *nix
			$cmd = shell_exec( 'ping  -c 4 ' . escapeshellarg( $target ) );
		}

		// Feedback for the end user
		$html .= "<pre>" . htmlspecialchars( $cmd, ENT_QUOTES, 'UTF-8' ) . "</pre>";
	}
	else {
		// Oops. Let the user know theres a mistake
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
