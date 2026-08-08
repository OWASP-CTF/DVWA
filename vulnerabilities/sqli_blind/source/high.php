<?php

if( isset( $_COOKIE[ 'id' ] ) ) {
	// Get input
	$id = $_COOKIE[ 'id' ];
	$exists = false;

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;";
			try {
				$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], $query );
				mysqli_stmt_bind_param( $stmt, 's', $id );
				mysqli_stmt_execute( $stmt );
				mysqli_stmt_store_result( $stmt );
				$exists = ( mysqli_stmt_num_rows( $stmt ) > 0 );
				mysqli_stmt_close( $stmt );
			} catch (Exception $e) {
				$exists = false;
			}

			((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;";
			try {
				$stmt = $sqlite_db_connection->prepare( $query );
				$stmt->bindValue( ':id', $id, SQLITE3_TEXT );
				$results = $stmt->execute();
				if( $results !== false ) {
					$row = $results->fetchArray();
					$exists = ( $row !== false );
					$results->finalize();
				}
			} catch(Exception $e) {
				$exists = false;
			}

			break;
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
