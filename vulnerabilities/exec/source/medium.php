<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = filter_input(INPUT_POST, 'ip', FILTER_VALIDATE_IP);
	if ($target === false || $target === null) {
		$html .= '<pre>Invalid IP address.</pre>';
		exit;
	}

	// Determine OS and execute the ping command.
	if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
		// Windows
		$cmd = shell_exec( 'ping ' . escapeshellarg($target) );
	}
	else {
		// *nix
		$cmd = shell_exec( 'ping -c 4 ' . escapeshellarg($target) );
	}

	// Feedback for the end user
	$html .= "<pre>{$cmd}</pre>";
}

?>
