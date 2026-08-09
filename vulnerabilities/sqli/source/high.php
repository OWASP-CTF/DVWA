<?php

if( isset( $_SESSION [ 'id' ] ) ) {
	// Get input
	$id = $_SESSION[ 'id' ];

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database using a parameterised query
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;";
			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
			mysqli_stmt_bind_param($stmt, 's', $id);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
			if ($result === false) {
				die('<pre>Something went wrong.</pre>');
			}

			// Get results
			while( $row = mysqli_fetch_assoc( $result ) ) {
				// Get values
				$first = $row["first_name"];
				$last  = $row["last_name"];

				// Feedback for end user. Encode for the HTML context, since
				// loose type coercion in the query above does not guarantee
				// $id is a plain number by the time it gets here.
				$html .= "<pre>ID: " . htmlspecialchars( (string) $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( (string) $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( (string) $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
			}

			mysqli_stmt_close($stmt);
			((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;";
			#print $query;
			try {
				$stmt = $sqlite_db_connection->prepare($query);
				$stmt->bindValue(':id', $id, SQLITE3_TEXT);
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
					$html .= "<pre>ID: " . htmlspecialchars( (string) $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( (string) $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( (string) $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
				}
			} else {
				echo "Error in fetch ".$sqlite_db->lastErrorMsg();
			}
			break;
	}
}

?>
