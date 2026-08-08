<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
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

				// Feedback for end user. $id is encoded even though it is bound
				// as a query parameter (so it can never be parsed as SQL) -
				// MySQL's loose numeric coercion can still match a row like
				// user_id = 1 against a string such as "1<script>...", and
				// that raw string would otherwise be reflected unescaped.
				$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
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

					// Feedback for end user (encoded - see the MySQL branch above
					// for why $id itself needs this even though it's bound as a
					// query parameter).
					$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: " . htmlspecialchars( $first, ENT_QUOTES, 'UTF-8' ) . "<br />Surname: " . htmlspecialchars( $last, ENT_QUOTES, 'UTF-8' ) . "</pre>";
				}
			} else {
				echo "Error in fetch ".$sqlite_db->lastErrorMsg();
			}
			break;
	}
}

?>
