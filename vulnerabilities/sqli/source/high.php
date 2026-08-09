<?php

if( isset( $_SESSION [ 'id' ] ) ) {
	// Get input
	$id = $_SESSION[ 'id' ];

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;";
			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query) or die( '<pre>Something went wrong.</pre>' );
			mysqli_stmt_bind_param( $stmt, 's', $id );
			mysqli_stmt_execute( $stmt ) or die( '<pre>Something went wrong.</pre>' );
			$result = mysqli_stmt_get_result( $stmt );

			// Get results
			while( $row = mysqli_fetch_assoc( $result ) ) {
				// Get values
				$first = $row["first_name"];
				$last  = $row["last_name"];

				// Feedback for end user
				$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
			}

			((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);		
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;";
			#print $query;
			try {
				$stmt = $sqlite_db_connection->prepare($query);
				$results = false;
				if ($stmt) {
					$stmt->bindValue(':id', $id, SQLITE3_TEXT);
					$results = $stmt->execute();
				}
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

?>
