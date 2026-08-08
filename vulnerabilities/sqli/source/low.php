<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );
	// Get input
	$id = $_REQUEST[ 'id' ];

	// Was a number entered?
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database (parameterised query - no user input in the SQL text)
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id) LIMIT 1;' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();
				$row = $data->fetch();

				// Make sure only 1 result is returned
				if( $data->rowCount() == 1 ) {
					// Get values
					$first = $row[ 'first_name' ];
					$last  = $row[ 'last_name' ];

					// Feedback for end user (output encoded to prevent reflected XSS)
					$html .= "<pre>ID: " . htmlspecialchars( $id ) . "<br />First name: " . htmlspecialchars( $first ) . "<br />Surname: " . htmlspecialchars( $last ) . "</pre>";
				}
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;' );
				$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
				try {
					$result = $stmt->execute();
				} catch (Exception $e) {
					echo 'Caught exception: ' . $e->getMessage();
					exit();
				}

				if ($result !== false) {
					$row = $result->fetchArray();
					if ($row) {
						// Get values
						$first = $row["first_name"];
						$last  = $row["last_name"];

						// Feedback for end user (output encoded to prevent reflected XSS)
						$html .= "<pre>ID: " . htmlspecialchars( $id ) . "<br />First name: " . htmlspecialchars( $first ) . "<br />Surname: " . htmlspecialchars( $last ) . "</pre>";
					}
				} else {
					echo "Error in fetch ".$sqlite_db->lastErrorMsg();
				}
				break;
		}
	}
}

generateSessionToken();

?>
