<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim($_REQUEST[ 'ip' ]);

	// Set blacklist
	$substitutions = array(
		'||' => '',
		'&'  => '',
		';'  => '',
		'| ' => '',
		'-'  => '',
		'$'  => '',
		'('  => '',
		')'  => '',
		'`'  => '',
	);

	// Remove any of the characters in the array (blacklist).
	$target = str_replace( array_keys( $substitutions ), $substitutions, $target );

	// Blacklist alone doesn't stop injection, so require a well formed IPv4 address too
	$octet = explode( ".", $target );
	if( ( is_numeric( $octet[0] ?? null ) ) && ( is_numeric( $octet[1] ?? null ) ) && ( is_numeric( $octet[2] ?? null ) ) && ( is_numeric( $octet[3] ?? null ) ) && ( sizeof( $octet ) == 4 ) ) {
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
		// Ops. Let the user name theres a mistake
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
