<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$id = $_POST[ 'id' ];
	$exists = false;

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database, using a prepared statement so the input can never
			// be parsed as SQL.
			try {
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id);' );
				$data->bindValue( ':id', (int)$id, PDO::PARAM_INT );
				$data->execute();
				$exists = ( $data->fetch() !== false );
			} catch (Exception $e) {
				$exists = false;
			}

			break;
		case SQLITE:
			global $sqlite_db_connection;

			try {
				$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id;' );
				$stmt->bindValue( ':id', (int)$id, SQLITE3_INTEGER );
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
