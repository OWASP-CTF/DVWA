<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = ( isset( $_REQUEST[ 'ip' ] ) && is_string( $_REQUEST[ 'ip' ] ) ) ? trim( $_REQUEST[ 'ip' ] ) : '';

	// Positive validation (allow-list): the input has to be a dotted-quad IPv4 address and nothing else.
	// Split the IP into 4 octets
	$octet = explode( ".", $target );
	$valid = ( count( $octet ) == 4 );

	if( $valid ) {
		foreach( $octet as $i => $o ) {
			// Each octet must be 1-3 decimal digits in the range 0-255.
			// ctype_digit() rejects signs, spaces, newlines, hex, exponents and every shell metacharacter.
			if( strlen( $o ) < 1 || strlen( $o ) > 3 || !ctype_digit( $o ) || intval( $o ) > 255 ) {
				$valid = false;
				break;
			}

			// Normalise (drops leading zeros so nothing is re-interpreted as octal).
			$octet[ $i ] = strval( intval( $o ) );
		}
	}

	if( $valid ) {
		// Rebuild the target from the validated octets - no user supplied byte reaches the shell.
		$target = $octet[0] . '.' . $octet[1] . '.' . $octet[2] . '.' . $octet[3];

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

?>
