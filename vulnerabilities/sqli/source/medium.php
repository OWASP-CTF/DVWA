<?php

if( isset( $_POST[ 'Submit' ] ) ) {
	// Get input
	$id = $_POST[ 'id' ];

	// Was a number entered?
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				$query  = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
				try {
					$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
					mysqli_stmt_bind_param($stmt, "i", $id);
					mysqli_stmt_execute($stmt);
					$result = mysqli_stmt_get_result($stmt);

					// Get results
					while( $row = mysqli_fetch_assoc( $result ) ) {
						// Display values
						$first = $row["first_name"];
						$last  = $row["last_name"];

						// Feedback for end user
						$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: {$first}<br />Surname: {$last}</pre>";
					}

					mysqli_stmt_close($stmt);
				} catch ( mysqli_sql_exception $e ) {
					$html .= '<pre>Something went wrong.</pre>';
				}
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$query  = "SELECT first_name, last_name FROM users WHERE user_id = :id;";
				#print $query;
				try {
					$stmt = $sqlite_db_connection->prepare( $query );
					$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
					$results = $stmt->execute();

					while ($row = $results->fetchArray()) {
						// Get values
						$first = $row["first_name"];
						$last  = $row["last_name"];

						// Feedback for end user
						$html .= "<pre>ID: " . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . "<br />First name: {$first}<br />Surname: {$last}</pre>";
					}

					$results->finalize();
				} catch (Exception $e) {
					$html .= '<pre>Something went wrong.</pre>';
				}
				break;
		}
	}
}

// This is used later on in the index.php page
// Setting it here so we can close the database connection in here like in the rest of the source scripts
$query  = "SELECT COUNT(*) FROM users;";
try {
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );
	$number_of_rows = mysqli_fetch_row( $result )[0];
} catch ( mysqli_sql_exception $e ) {
	$number_of_rows = 0;
	$html .= '<pre>Something went wrong.</pre>';
}

mysqli_close($GLOBALS["___mysqli_ston"]);
?>
