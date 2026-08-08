<?php

if( isset( $_SESSION [ 'id' ] ) ) {
	// Get input
	$id = $_SESSION[ 'id' ];

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database
			// The id is bound as a parameter, so the trailing LIMIT can no longer be
			// commented out (e.g. "1' OR 1=1 -- ") and no SQL can be smuggled in.
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;";
			$stmt   = mysqli_prepare($GLOBALS["___mysqli_ston"], $query ) or die( '<pre>Something went wrong.</pre>' );
			mysqli_stmt_bind_param( $stmt, 'i', $id );
			mysqli_stmt_execute( $stmt );
			mysqli_stmt_store_result( $stmt );
			mysqli_stmt_bind_result( $stmt, $first, $last );

			// Get results
			while( mysqli_stmt_fetch( $stmt ) ) {
				// Feedback for end user
				$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: {$first}<br />Surname: {$last}</pre>";
			}

			mysqli_stmt_close( $stmt );
			((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;";
			#print $query;
			try {
				$stmt = $sqlite_db_connection->prepare( $query );
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
					$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: {$first}<br />Surname: {$last}</pre>";
				}
			} else {
				echo "Error in fetch ".$sqlite_db->lastErrorMsg();
			}
			break;
	}
}

?>
