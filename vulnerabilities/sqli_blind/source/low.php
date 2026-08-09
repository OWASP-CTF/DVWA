<?php

if( isset( $_GET[ 'Submit' ] ) ) {
	// Get input
	$id = $_GET[ 'id' ];
	$exists = false;

	// The user_id column is only ever a plain integer, so reject anything
	// else outright instead of relying on parameterisation alone - this
	// also keeps the boolean-exists signal from being abusable via MySQL's
	// leading-numeric-prefix coercion of a non-numeric string.
	if( !is_numeric( $id ) ) {
		$exists = false;
	} else {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database using a parameterised query so the input can
				// never alter the structure of the SQL statement.
				$query = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
				try {
					$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], $query );
					mysqli_stmt_bind_param( $stmt, 'i', $id );
					mysqli_stmt_execute( $stmt );
					$result = mysqli_stmt_get_result( $stmt );
				} catch (Exception $e) {
					print "There was an error.";
					exit;
				}

				$exists = false;
				if ($result !== false) {
					try {
						$exists = (mysqli_num_rows( $result ) > 0);
					} catch(Exception $e) {
						$exists = false;
					}
				}
				((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$query = "SELECT first_name, last_name FROM users WHERE user_id = :id;";
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
	} else {
		// User wasn't found, so the page wasn't!
		header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );

		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}

}

?>
