<?php

if( isset( $_COOKIE[ 'id' ] ) ) {
	// Get input. cookie-input.php only ever writes a digits-only value, but
	// the cookie itself is still attacker-controlled - it can be sent
	// directly (e.g. via curl) without ever going through that page.
	$id = $_COOKIE[ 'id' ];
	$exists = false;

	// A prepared statement stops the value from ever being parsed as SQL, but
	// MySQL's loose type coercion when comparing a string against an INT
	// column (e.g. "1' OR '1'='1") can still numerically match a real row -
	// which would falsely report "exists" and hand a blind-SQLi oracle back
	// to the attacker even though no injection actually occurred. Only ever
	// run the lookup for a genuine whole number, exactly like impossible.php.
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database using a parameterised query
				$query  = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;";
				try {
					$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
					mysqli_stmt_bind_param($stmt, 'i', $id);
					mysqli_stmt_execute($stmt);
					$result = mysqli_stmt_get_result($stmt);
				} catch (Exception $e) {
					$result = false;
				}

				$exists = false;
				if ($result !== false) {
					// Get results
					try {
						$exists = (mysqli_num_rows( $result ) > 0); // The '@' character suppresses errors
					} catch(Exception $e) {
						$exists = false;
					}
				}

				((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;";
				try {
					$stmt = $sqlite_db_connection->prepare($query);
					$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
					$results = $stmt->execute();
					$row = $results->fetchArray();
					$exists = $row !== false;
				} catch(Exception $e) {
					$exists = false;
				}

				break;
		}
	}

	if ($exists) {
		// Feedback for end user
		$html .= '<pre>User ID exists in the database.</pre>';
	}
	else {
		// Might sleep a random amount
		if( rand( 0, 5 ) == 3 ) {
			sleep( rand( 2, 4 ) );
		}

		// User wasn't found, so the page wasn't!
		header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );

		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

?>
