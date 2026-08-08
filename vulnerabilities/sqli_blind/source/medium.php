<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$id = $_POST[ 'id' ];
	$exists = false;

	// The drop-down only ever offers numeric record ids; validate rather than
	// relying on string escaping.
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database with a prepared statement
				$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT COUNT(first_name) AS numrows FROM users WHERE user_id = ? LIMIT 1;" );

				if( $stmt ) {
					mysqli_stmt_bind_param( $stmt, "i", $id );
					mysqli_stmt_execute( $stmt );
					$result = mysqli_stmt_get_result( $stmt );

					if( $result && ( $row = mysqli_fetch_assoc( $result ) ) ) {
						$exists = ( $row[ 'numrows' ] > 0 );
					}

					mysqli_stmt_close( $stmt );
				}

				break;
			case SQLITE:
				global $sqlite_db_connection;

				try {
					$stmt = $sqlite_db_connection->prepare( 'SELECT COUNT(first_name) AS numrows FROM users WHERE user_id = :id LIMIT 1;' );
					$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
					$results = $stmt->execute();
					$row = $results->fetchArray();
					$exists = ( $row !== false && $row[ 'numrows' ] > 0 );
				} catch(Exception $e) {
					$exists = false;
				}
				break;
		}
	}

	if ($exists) {
		// Feedback for end user
		$html .= '<pre>User ID exists in the database.</pre>';
	} else {
		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
