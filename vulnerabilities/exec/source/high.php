<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim($_REQUEST[ 'ip' ]);

	// Only permit a valid IPv4 address to reach the command shell.
	if( filter_var( $target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) === false ) {
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
		return;
	}

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

?>
