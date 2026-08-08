<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim( stripslashes( $_REQUEST[ 'ip' ] ) );

	// Only a well-formed IPv4 address is a legitimate ping target. Validating
	// against an allowlist beats trying to blacklist shell metacharacters.
	if( filter_var( $target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false ) {
		// Belt and braces: the argument is also quoted for the shell.
		$safe_target = escapeshellarg( $target );

		// Determine OS and execute the ping command.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			// Windows
			$cmd = shell_exec( 'ping  ' . $safe_target );
		}
		else {
			// *nix
			$cmd = shell_exec( 'ping  -c 4 ' . $safe_target );
		}

		// Feedback for the end user
		$html .= "<pre>" . htmlspecialchars( $cmd, ENT_QUOTES, 'UTF-8' ) . "</pre>";
	}
	else {
		// Let the user know they made a mistake
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
