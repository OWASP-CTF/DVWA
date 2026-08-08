<?php

if( isset( $_GET[ 'Submit' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );
	// Get input
	$id = $_GET[ 'id' ];
	$exists = false;

	// Only allow a numeric user_id through. This removes any possibility of
	// attacker-controlled SQL syntax reaching the query (boolean- and
	// time-based blind injection alike), while still accepting every
	// legitimate id (the column is an INT).
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				global $db;

				// Check database
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id);' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();

				$exists = ( $data->rowCount() > 0 );
				break;
			case SQLITE:
				global $sqlite_db_connection;

				try {
					$stmt = $sqlite_db_connection->prepare( 'SELECT COUNT(first_name) AS numrows FROM users WHERE user_id = :id;' );
					$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
					$result = $stmt->execute();
					if ( $result !== false ) {
						$row    = $result->fetchArray();
						$exists = ( isset( $row[ 'numrows' ] ) && $row[ 'numrows' ] > 0 );
					}
				} catch ( Exception $e ) {
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

generateSessionToken();

?>
