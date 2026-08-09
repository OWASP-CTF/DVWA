<?php

if( isset( $_GET[ 'Submit' ] ) ) {
	// Get input
	$id = $_GET[ 'id' ];
	$exists = false;

	if( is_string( $id ) && ctype_digit( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Check database
				$query = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
				try {
					$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
					mysqli_stmt_bind_param($stmt, 'i', $id);
					mysqli_stmt_execute($stmt);
					$result = mysqli_stmt_get_result($stmt); // Removed 'or die' to suppress mysql errors
				} catch (Exception $e) {
					print "There was an error.";
					exit;
				}

				$exists = false;
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

				$query = "SELECT first_name, last_name FROM users WHERE user_id = :id;";
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
