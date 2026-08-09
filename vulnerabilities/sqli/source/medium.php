<?php

if( isset( $_POST[ 'Submit' ] ) ) {
	// Get input
	$id = $_POST[ 'id' ];

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// mysqli_real_escape_string only helps when the value is quoted in the query - this
			// query had no surrounding quotes around $id, so escaping alone did nothing to stop
			// a numeric-context injection like "1 OR 1=1".
			$query = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
			$stmt  = mysqli_prepare($GLOBALS["___mysqli_ston"], $query) or die( '<pre>' . mysqli_error($GLOBALS["___mysqli_ston"]) . '</pre>' );
			mysqli_stmt_bind_param($stmt, 's', $id);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);

			// Get results
			while( $row = mysqli_fetch_assoc( $result ) ) {
				// Display values
				$first = $row["first_name"];
				$last  = $row["last_name"];

				// Feedback for end user
				$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
			}
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$query = "SELECT first_name, last_name FROM users WHERE user_id = :id;";
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
					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}
			} else {
				echo "Error in fetch ".$sqlite_db->lastErrorMsg();
			}
			break;
	}
}

// This is used later on in the index.php page
// Setting it here so we can close the database connection in here like in the rest of the source scripts
$query  = "SELECT COUNT(*) FROM users;";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );
$number_of_rows = mysqli_fetch_row( $result )[0];

mysqli_close($GLOBALS["___mysqli_ston"]);
?>
