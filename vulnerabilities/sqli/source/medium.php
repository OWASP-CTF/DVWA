<?php

if( isset( $_POST[ 'Submit' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );
	// Get input
	$id = $_POST[ 'id' ];

	// Was a number entered? (the dropdown only ever submits digits, but the
	// handler must not trust that — validate server-side too)
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Parameterised query - the id is bound as data, never concatenated into SQL text
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id) LIMIT 1;' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();
				$row = $data->fetch();

				if( $data->rowCount() == 1 ) {
					// Display values
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

// This is used later on in the index.php page
// Setting it here so we can close the database connection in here like in the rest of the source scripts
$query  = "SELECT COUNT(*) FROM users;";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );
$number_of_rows = mysqli_fetch_row( $result )[0];

mysqli_close($GLOBALS["___mysqli_ston"]);
?>
