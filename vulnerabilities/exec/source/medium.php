<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = $_REQUEST[ 'ip' ];
	$target = stripslashes( $target );

	// Validate: split input on '.' and verify exactly 4 numeric octets (whitelist approach).
	// This replaces the previous blacklist which was bypassable via '|', newline, and other separators.
	$octet = explode( ".", $target );

	if( ( is_numeric( $octet[0] ) ) && ( is_numeric( $octet[1] ) ) && ( is_numeric( $octet[2] ) ) && ( is_numeric( $octet[3] ) ) && ( sizeof( $octet ) == 4 ) ) {
		// Reconstruct a safe, validated IP from the individual octets
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
		// Input is not a valid IP address — reject it.
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
