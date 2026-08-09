<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
    // Get input
    $id = $_REQUEST[ 'id' ];

    // SECURE FIX: Prepared statement to prevent SQL Injection
    $query  = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
    $stmt   = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);

    if ($stmt) {
        // Bind parameter as integer/string safely
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Get results
        while( $row = mysqli_fetch_assoc( $result ) ) {
            $first = $row["first_name"];
            $last  = $row["last_name"];

            // Feedback for user
            echo "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "<pre>Database query error.</pre>";
    }

    mysqli_close($GLOBALS["___mysqli_ston"]);
}

?>