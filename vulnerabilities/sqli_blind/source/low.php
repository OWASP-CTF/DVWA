<?php

if( isset( $_GET[ 'Submit' ] ) ) {
	// Get input
	$id = $_GET[ 'id' ];
	$exists = false;

	// Only ever look up a whole number, so nothing else can reach the query.
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database, using a prepared statement so the input can
				// never be parsed as SQL.
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id) LIMIT 1;' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();

				$exists = ( $data->rowCount() == 1 );

				((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$stmt = $sqlite_db_connection->prepare( 'SELECT COUNT(first_name) AS numrows FROM users WHERE user_id = :id LIMIT 1;' );
				$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
				$result = $stmt->execute();
				if( $result !== false ) {
					$row = $result->fetchArray();
					$exists = ( $row[ 'numrows' ] == 1 );
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
