<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim($_REQUEST[ 'ip' ]);

	// Input validation: a hostname/IP only ever needs letters, digits, dots,
	// hyphens and colons (IPv6). Enumerating individual "bad" shell
	// metacharacters/sequences (as the previous blacklist did) is always
	// incomplete - allowlisting the legitimate character set is not.
	if( !preg_match( '/^[a-zA-Z0-9.:_-]+$/', $target ) ) {
		$html .= '<pre>ERROR: Invalid IP/hostname supplied.</pre>';
	}
	else {
		// Determine OS and execute the ping command, with the validated
		// argument additionally shell-escaped as defence in depth.
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
