<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$id = $_POST[ 'id' ];
	$exists = false;

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database
			// The user supplied id is bound as a parameter, so it can never be parsed as SQL.
			// (Escaping is not used here: an escaped value dropped into an unquoted numeric
			// context is still injectable, e.g. "1 OR 1=1".)
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
			try {
				$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"],  $query ); // Removed 'or die' to suppress mysql errors
			} catch (Exception $e) {
				print "There was an error.";
				exit;
			}

			$exists = false;
			if ($stmt !== false) {
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

			break;
		case SQLITE:
			global $sqlite_db_connection;

			$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id;";
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

	if ($exists) {
		// Feedback for end user
		$html .= '<pre>User ID exists in the database.</pre>';
	} else {
		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

?>
