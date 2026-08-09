<?php

if( isset( $_COOKIE[ 'id' ] ) ) {
	// Get input
	$id = $_COOKIE[ 'id' ];
	$exists = false;

	// The entire cookie must be a decimal ID before it is converted. Otherwise
	// an injected value with a numeric prefix is silently coerced to that user
	// and continues to expose an EXISTS/MISSING oracle.
	if( is_string( $id ) && preg_match( '/^[0-9]{1,10}\z/', $id ) === 1 ) {
		$id = (int)$id;

		switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database
			// The cookie value is bound as a parameter, so the trailing LIMIT can no longer be
			// commented out and no time based payload (SLEEP/BENCHMARK) can be smuggled in.
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;";
			try {
				$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"],  $query ); // Removed 'or die' to suppress mysql errors
			} catch (Exception $e) {
				$stmt = false;
			}

			$exists = false;
			if ($stmt !== false) {
				// Get results
				try {
					mysqli_stmt_bind_param( $stmt, 'i', $id );
					mysqli_stmt_execute( $stmt );
					mysqli_stmt_store_result( $stmt );
					$exists = (mysqli_stmt_num_rows( $stmt ) > 0);
				} catch(Exception $e) {
					$exists = false;
				}
				mysqli_stmt_close( $stmt );
			}

			((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;";
			try {
				$stmt = $sqlite_db_connection->prepare( $query );
				$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
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

		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

?>
