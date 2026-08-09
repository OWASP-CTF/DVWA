<?php

if( isset( $_GET[ 'Submit' ] ) ) {
	// Get input
	$id = $_GET[ 'id' ];
	$exists = false;

	// Was a number entered? Reject anything else outright, rather than
	// binding it as a string - the user_id column is numeric, so a
	// non-numeric value can never legitimately match a row anyway.
	if (is_numeric ($id)) {
		$id = intval ($id);
		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database using a parameterised query
				$query  = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
				try {
					$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
					mysqli_stmt_bind_param($stmt, 'i', $id);
					mysqli_stmt_execute($stmt);
					$result = mysqli_stmt_get_result($stmt);
				} catch (Exception $e) {
					print "There was an error.";
					exit;
				}

				if ($result !== false) {
					try {
						$exists = (mysqli_num_rows( $result ) > 0);
					} catch(Exception $e) {
						$exists = false;
					}
				}
				((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id;";
				try {
					$stmt = $sqlite_db_connection->prepare($query);
					$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
					$results = $stmt->execute();
					$row = $results->fetchArray();
					$exists = $row !== false;
				} catch(Exception $e) {
					$exists = false;
				}

				break;
		}
	}

	if ($exists) {
		// Feedback for end user
		$html .= '<pre>User ID exists in the database.</pre>';
	} else {
		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}

}

?>
