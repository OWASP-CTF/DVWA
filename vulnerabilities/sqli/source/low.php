<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	// Was a number entered?
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id);' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();

				// Get results
				while( $row = $data->fetch() ) {
					// Get values
					$first = $row["first_name"];
					$last  = $row["last_name"];

					// Feedback for end user
					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}

				mysqli_close($GLOBALS["___mysqli_ston"]);
				break;
			case SQLITE:
				global $sqlite_db_connection;

				try {
					$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id;' );
					$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
					$results = $stmt->execute();
				} catch (Exception $e) {
					echo 'Caught exception: ' . $e->getMessage();
					exit();
				}

				if ($results) {
					while ($row = $results->fetchArray()) {
						// Get values
						$first = $row["first_name"];
						$last  = $row["last_name"];

						// Feedback for end user
						$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
					}
				} else {
					echo "Error in fetch ".$sqlite_db->lastErrorMsg();
				}
				break;
		}
	}
}

?>
