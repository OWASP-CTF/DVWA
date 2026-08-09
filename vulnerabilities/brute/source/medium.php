<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username and password
	$user = $_GET[ 'username' ];
	$pass = $_GET[ 'password' ];

	// Check the database using a prepared statement
	$query  = "SELECT * FROM `users` WHERE user = ? AND password = MD5(?)";
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
	if (!$stmt) {
		$html .= "<pre><br />Database error.</pre>";
		return;
	}
	mysqli_stmt_bind_param($stmt, "ss", $user, $pass);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if( $result && mysqli_num_rows( $result ) == 1 ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$avatar = $row["avatar"];

		// Login successful
		$html .= "<p>Welcome to the password protected area " . htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . "</p>";
		$html .= "<img src=\"" . htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') . "\" />";
	}
	else {
		// Login failed - small delay to slow brute force
		sleep( 2 );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}

	mysqli_stmt_close($stmt);
	mysqli_close($GLOBALS["___mysqli_ston"]);
}

?>
