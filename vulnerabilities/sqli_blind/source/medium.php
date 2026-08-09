<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$id = $_POST[ 'id' ];
	$exists = false;

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// user_id is expected numeric; enforce it before it reaches the query
			$id = intval($id);

			// Check database
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
			$exists = false;
			try {
				$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
				if ($stmt !== false) {
					mysqli_stmt_bind_param($stmt, 'i', $id);
					mysqli_stmt_execute($stmt);
					mysqli_stmt_store_result($stmt);
					$exists = (mysqli_stmt_num_rows( $stmt ) > 0); // The '@' character suppresses errors
				}
			} catch (Exception $e) {
				print "There was an error.";
				exit;
			}

			break;
		case SQLITE:
			global $sqlite_db_connection;

			$id = intval($id);

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

	if ($exists) {
		// Feedback for end user
		$html .= '<pre>User ID exists in the database.</pre>';
	} else {
		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

?>
