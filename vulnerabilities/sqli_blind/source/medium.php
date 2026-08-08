<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );
	// Get input
	$id = $_POST[ 'id' ];
	$exists = false;

	// The dropdown only ever sends a plain integer, but the request is
	// attacker controlled ($_REQUEST is used elsewhere in DVWA, so this must
	// hold for GET as well as POST). Reject anything that isn't numeric
	// instead of merely escaping it, since the original query had no quotes
	// around the value and escaping alone does not stop numeric-context
	// injection (e.g. "1 AND SLEEP(5)").
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
		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

generateSessionToken();

?>
