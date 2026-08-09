<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim($_REQUEST[ 'ip' ]);

	// The blacklist blocked "||" and "| " (with trailing space) but not a bare "|" - e.g.
	// "127.0.0.1|whoami" still reached shell_exec() unfiltered. Split the IP into 4 octets and
	// check each is numeric instead, so no metacharacter can ever reach the command.
	$octet = explode( ".", $target );

	if( ( is_numeric( $octet[0] ) ) && ( is_numeric( $octet[1] ) ) && ( is_numeric( $octet[2] ) ) && ( is_numeric( $octet[3] ) ) && ( sizeof( $octet ) == 4 ) ) {
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

		// Feedback for the end user
		$html .= "<pre>{$cmd}</pre>";
	}
	else {
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
