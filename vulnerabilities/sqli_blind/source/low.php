<?php

if( isset( $_GET[ 'Submit' ] ) ) {
	// Get input
	$id = $_GET[ 'id' ];
	$exists = false;

	// Was a number entered?
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database -- the user id is bound as a parameter, so it
				// is never parsed as SQL and cannot be used to probe the database.
				try {
					$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id);' );
					$data->bindParam( ':id', $id, PDO::PARAM_INT );
					$data->execute();
					$exists = ( $data->rowCount() > 0 );
				} catch (Exception $e) {
					$exists = false;
				}

				((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
				break;
			case SQLITE:
				global $sqlite_db_connection;

				try {
					$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id;' );
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
