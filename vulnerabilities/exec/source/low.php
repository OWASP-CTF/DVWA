<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = $_REQUEST[ 'ip' ];

	// PATCH (CTF): only ping a syntactically valid IP address. Injection payloads
	// such as `127.0.0.1;id` fail validation and never reach shell_exec, so the
	// page still returns 200 but leaks no command output.
	if( filter_var( $target, FILTER_VALIDATE_IP ) ) {
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
}

?>
