<?php

if( isset( $_POST[ 'Submit' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$id = $_POST[ 'id' ];

	// Escaping was useless here because the value was interpolated without
	// quotes, so a payload never needed a quote to break out. Validate the
	// type and bind the value instead.
	if( !is_numeric( $id ) ) {
		$html .= '<pre>ID must be a number.</pre>';
	}
	else {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id) LIMIT 1;' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();
				$row = $data->fetch();

				if( $row ) {
					// Display values
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

// This is used later on in the index.php page to build the dropdown. The
// handle raises on error, so a database problem here would otherwise be a
// blank 500 rather than a page.
try {
	$number_of_rows = (int) $db->query( 'SELECT COUNT(*) FROM users;' )->fetchColumn();
}
catch ( PDOException $e ) {
	error_log( 'sqli/medium: could not count the users: ' . $e->getMessage() );
	$number_of_rows = 0;
}

// Generate Anti-CSRF token
generateSessionToken();

?>
