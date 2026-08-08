<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim( $_REQUEST[ 'ip' ] );

	// Only accept a well-formed IPv4 address. The previous character blacklist
	// (||, &, ;, -, $, (, ), `, ...) is an incomplete denylist and can be bypassed
	// (e.g. newlines, other metacharacters); validate the whole value instead.
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

?>
