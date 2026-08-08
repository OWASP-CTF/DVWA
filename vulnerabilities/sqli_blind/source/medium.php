<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$id = $_POST[ 'id' ];
	$exists = false;

	// The id is a number, so anything trailing it is not part of the lookup
	$id = intval( $id );

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database
			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ?;");
			try {
				$result = false;
				if ($stmt !== false) {
					mysqli_stmt_bind_param($stmt, "i", $id);
					mysqli_stmt_execute($stmt);
					$result = mysqli_stmt_get_result($stmt);
				}
			} catch (Exception $e) {
				print "There was an error.";
				exit;
			}

			$exists = false;
			if ($result !== false) {
				try {
					$exists = (mysqli_num_rows( $result ) > 0); // The '@' character suppresses errors
				} catch(Exception $e) {
					$exists = false;
				}
			}
			
			break;
		case SQLITE:
			global $sqlite_db_connection;
			
			try {
				$stmt = $sqlite_db_connection->prepare("SELECT first_name, last_name FROM users WHERE user_id = :id;");
				$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
				$results = $stmt->execute();
				$row = $results === false ? false : $results->fetchArray();
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
