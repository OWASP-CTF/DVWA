<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim( $_REQUEST[ 'ip' ] );

	// Only a bare IPv4/IPv6 address is a legitimate value here - anything
	// else (shell metacharacters, hostnames, etc.) is rejected outright so
	// it can never reach the shell command below.
	if( filter_var( $target, FILTER_VALIDATE_IP ) === false ) {
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	} else {
		// Determine OS and execute the ping command. escapeshellarg() is
		// used as defense in depth even though $target is already confirmed
		// to be a plain IP address.
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
}

?>
