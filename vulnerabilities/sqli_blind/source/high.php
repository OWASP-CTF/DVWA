<?php

if( isset( $_COOKIE[ 'id' ] ) ) {
	// Get input
	$id = $_COOKIE[ 'id' ];
	$exists = false;

	// Was a number entered? The id comes from an attacker controlled cookie,
	// so validate it and bind it as a parameter rather than interpolating it.
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database
				try {
					$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id) LIMIT 1;' );
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
					$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;' );
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

		// User wasn't found, so the page wasn't!
		header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );

		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

?>
