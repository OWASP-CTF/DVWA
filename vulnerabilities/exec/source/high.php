<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = stripslashes( trim( $_REQUEST[ 'ip' ] ) );

	// Split the IP into 4 octets and require each to be an integer, so the value
	// handed to the shell can only ever be a numeric dotted-quad with no metacharacters.
	$octet = explode( ".", $target );

	if( ( count( $octet ) == 4 ) && is_numeric( $octet[0] ) && is_numeric( $octet[1] ) && is_numeric( $octet[2] ) && is_numeric( $octet[3] ) ) {
		$target = (int)$octet[0] . '.' . (int)$octet[1] . '.' . (int)$octet[2] . '.' . (int)$octet[3];

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
		// Ops. Let the user know theres a mistake
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
