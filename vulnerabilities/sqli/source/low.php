<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], 'SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;');
				mysqli_stmt_bind_param($stmt, 'i', $id);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);

				while( $row = mysqli_fetch_assoc( $result ) ) {
					$first = $row["first_name"];
					$last  = $row["last_name"];

					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}

				mysqli_stmt_close($stmt);
				mysqli_close($GLOBALS["___mysqli_ston"]);
				break;
			case SQLITE:
				global $sqlite_db_connection;

				$stmt = $sqlite_db_connection->prepare('SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;');
				$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
				$result = $stmt->execute();

				while( $row = $result->fetchArray() ) {
					$first = $row["first_name"];
					$last  = $row["last_name"];

					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}

				$result->finalize();
				break;
		}
	}
}

?>
