<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$id = $_POST[ 'id' ];
	$exists = false;

	// The select control submits positive decimal user IDs. Validate that shape
	// before binding so database type coercion cannot turn an injection string
	// beginning with a valid ID into a match.
	$valid_id = is_string( $id ) && ctype_digit( $id ) && filter_var( $id, FILTER_VALIDATE_INT, array(
		'options' => array( 'min_range' => 1 )
	) ) !== false;

	if( $valid_id ) switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database
			try {
				$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ?;");
				mysqli_stmt_bind_param($stmt, "i", $id);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				$exists = (mysqli_stmt_num_rows($stmt) > 0);
				mysqli_stmt_close($stmt);
			} catch (Exception $e) {
				print "There was an error.";
				exit;
			}
			break;
		case SQLITE:
			global $sqlite_db_connection;

			try {
				$stmt = $sqlite_db_connection->prepare("SELECT first_name, last_name FROM users WHERE user_id = :id;");
				$stmt->bindValue(":id", $id, SQLITE3_INTEGER);
				$results = $stmt->execute();
				$row = $results->fetchArray();
				$exists = $row !== false;
				$results->finalize();
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
