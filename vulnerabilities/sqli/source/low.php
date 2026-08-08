<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$id = $_REQUEST[ 'id' ];

	// A user id is always a number. Reject anything else before it gets near
	// the database.
	if( !is_numeric( $id ) ) {
		$html .= '<pre>ID must be a number.</pre>';
	}
	else {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Bound parameter: the value is sent separately from the
				// statement, so it can never be parsed as SQL.
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id) LIMIT 1;' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();
				$row = $data->fetch();

				if( $row ) {
					// Get values
					$first = htmlspecialchars( $row[ 'first_name' ], ENT_QUOTES, 'UTF-8' );
					$last  = htmlspecialchars( $row[ 'last_name' ], ENT_QUOTES, 'UTF-8' );

					// Feedback for end user
					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;' );
				$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
				$results = $stmt->execute();

				if( $results !== false ) {
					$row = $results->fetchArray();
					if( $row ) {
						// Get values
						$first = htmlspecialchars( $row[ 'first_name' ], ENT_QUOTES, 'UTF-8' );
						$last  = htmlspecialchars( $row[ 'last_name' ], ENT_QUOTES, 'UTF-8' );

						// Feedback for end user
						$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
					}
				}
				break;
		}
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
