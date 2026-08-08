<?php

if( isset( $_POST[ 'Submit' ] ) ) {
	// Get input
	$id = $_POST[ 'id' ];

	// The drop-down only ever offers numeric record ids; reject anything else
	// rather than relying on string escaping.
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;" );

				if( $stmt ) {
					mysqli_stmt_bind_param( $stmt, "i", $id );
					mysqli_stmt_execute( $stmt );
					$result = mysqli_stmt_get_result( $stmt );

					// Get results
					while( $result && ( $row = mysqli_fetch_assoc( $result ) ) ) {
						// Display values
						$first = $row["first_name"];
						$last  = $row["last_name"];

						// Feedback for end user (escaped on the way out)
						$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
					}

					mysqli_stmt_close( $stmt );
				}
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

// This is used later on in the index.php page
// Setting it here so we can close the database connection in here like in the rest of the source scripts
$query  = "SELECT COUNT(*) FROM users;";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );
$number_of_rows = mysqli_fetch_row( $result )[0];

mysqli_close($GLOBALS["___mysqli_ston"]);
?>
