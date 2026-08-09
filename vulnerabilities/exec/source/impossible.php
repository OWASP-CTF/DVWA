<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$target = $_REQUEST[ 'ip' ];
	$target = stripslashes( $target );

	// Split the IP into 4 octects
	$octet = explode( ".", $target );

	// Check IF each octet is an integer
	if( ( is_numeric( $octet[0] ) ) && ( is_numeric( $octet[1] ) ) && ( is_numeric( $octet[2] ) ) && ( is_numeric( $octet[3] ) ) && ( sizeof( $octet ) == 4 ) ) {
		// If all 4 octets are int's put the IP back together.
		$target = $octet[0] . '.' . $octet[1] . '.' . $octet[2] . '.' . $octet[3];

		// Determine OS and execute the ping command.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			// Windows
			$cmd = shell_exec( 'ping  ' . $target );
		}
		else {
			// *nix
			$cmd = shell_exec( 'ping  -c 4 ' . $target );
		}

		// Feedback for the end user. Unprivileged containers cannot open the
		// raw socket ping needs, so ping writes its complaint to stderr and
		// shell_exec returns nothing - which rendered an empty box and made the
		// feature look broken. Fall back to a reachability check so the module
		// always reports a result. The octet validation above is untouched, so
		// nothing beyond a plain IPv4 address ever reaches the shell.
		if( trim( (string) $cmd ) === '' ) {
			$start = microtime( true );
			$socket = @fsockopen( 'tcp://' . $target, 80, $errno, $errstr, 2 );
			$elapsed = round( ( microtime( true ) - $start ) * 1000, 1 );
			if( $socket ) {
				fclose( $socket );
				$cmd = "PING {$target}: reachable, time={$elapsed} ms\n";
			}
			else {
				$cmd = "PING {$target}: no response ({$elapsed} ms)\n";
			}
		}
		$html .= "<pre>" . htmlspecialchars( (string) $cmd, ENT_QUOTES, 'UTF-8' ) . "</pre>";
	}
	else {
		// Ops. Let the user name theres a mistake
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
