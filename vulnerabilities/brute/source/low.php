<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = $_GET[ 'username' ];

	// Get password
	$pass = $_GET[ 'password' ];

	// Check the database
	$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT * FROM `users` WHERE user = ?" ) or die( '<pre>' . mysqli_error($GLOBALS["___mysqli_ston"]) . '</pre>' );
	mysqli_stmt_bind_param( $stmt, 's', $user );
	mysqli_stmt_execute( $stmt );
	$result = mysqli_stmt_get_result( $stmt );
	$row    = $result ? mysqli_fetch_assoc( $result ) : null;

	if( $row && dvwaVerifyPassword( $pass, $row[ 'password' ], $needsUpgrade ) ) {
		if( $needsUpgrade ) {
			$newHash = dvwaHashPassword( $pass );
			$upd = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE `users` SET password = ? WHERE user = ?" );
			mysqli_stmt_bind_param( $upd, 'ss', $newHash, $user );
			mysqli_stmt_execute( $upd );
		}
		// Get users details
		$avatar = $row["avatar"];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";
	}
	else {
		// Login failed
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
