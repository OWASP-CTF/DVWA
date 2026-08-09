<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Check Anti-CSRF token
	if (array_key_exists ("session_token", $_SESSION)) {
		$session_token = $_SESSION[ 'session_token' ];
	} else {
		$session_token = "";
	}
	checkToken( $_REQUEST[ 'user_token' ], $session_token, 'index.php' );

	// Get input
	$id = $_REQUEST[ 'id' ];

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check database using a parameterised query
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
			mysqli_stmt_bind_param($stmt, 's', $id);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
			if ($result === false) {
				die('<pre>' . mysqli_error($GLOBALS["___mysqli_ston"]) . '</pre>');
			}

			// Get results
			while( $row = mysqli_fetch_assoc( $result ) ) {
				// Get values
				$first = $row["first_name"];
				$last  = $row["last_name"];

				// Feedback for end user. MySQL's loose type coercion lets a
				// value like "1<script>" still match the numeric row for id
				// 1, so $id reaching here is not guaranteed to be a plain
				// number - encode everything echoed back for the HTML
				// context, not just the values that came from the database.
				$html .= "<pre>ID: " . htmlspecialchars( (string) $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( (string) $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( (string) $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
			}

			mysqli_stmt_close($stmt);
			mysqli_close($GLOBALS["___mysqli_ston"]);
			break;
		case SQLITE:
			global $sqlite_db_connection;

			#$sqlite_db_connection = new SQLite3($_DVWA['SQLITE_DB']);
			#$sqlite_db_connection->enableExceptions(true);

			$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id;";
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

					// Feedback for end user. Encode for the HTML context, since
					// $id can reach here as something other than a plain
					// number thanks to loose type coercion in the query above.
					$html .= "<pre>ID: " . htmlspecialchars( (string) $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( (string) $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( (string) $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
				}
			} else {
				echo "Error in fetch ".$sqlite_db->lastErrorMsg();
			}
			break;
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
