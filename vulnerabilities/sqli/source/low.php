<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	// Only a numeric record id is ever a legitimate lookup value.
	// Anything else is discarded before it can reach the database.
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database using a prepared statement so the id can
				// never be parsed as part of the SQL text.
				$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;" );

				if( $stmt ) {
					mysqli_stmt_bind_param( $stmt, "i", $id );
					mysqli_stmt_execute( $stmt );
					$result = mysqli_stmt_get_result( $stmt );

					// Get results
					while( $result && ( $row = mysqli_fetch_assoc( $result ) ) ) {
						// Get values
						$first = $row["first_name"];
						$last  = $row["last_name"];

						// Feedback for end user (escaped on the way out)
						$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
					}

					mysqli_stmt_close( $stmt );
				}

				mysqli_close($GLOBALS["___mysqli_ston"]);
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;' );

				try {
					$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
					$results = $stmt->execute();
				} catch (Exception $e) {
					echo 'Caught exception: ' . htmlspecialchars( $e->getMessage(), ENT_QUOTES, 'UTF-8' );
					exit();
				}

				if ($results) {
					while ($row = $results->fetchArray()) {
						// Get values
						$first = $row["first_name"];
						$last  = $row["last_name"];

						// Feedback for end user (escaped on the way out)
						$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
					}
				}
				break;
		}
	}
}

?>
