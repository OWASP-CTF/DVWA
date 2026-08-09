<?php

if( isset( $_GET[ 'Submit' ] ) ) {
    // Get input
    $id = $_GET[ 'id' ];

    // SECURE FIX: Prepared statement to prevent SQL Injection
    $query = "SELECT first_name, last_name FROM users WHERE user_id = ?;";
    $stmt  = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            echo '<pre>User ID exists in the database.</pre>';
        } else {
            echo '<pre>User ID is MISSING from the database.</pre>';
        }

        mysqli_stmt_close($stmt);
    } else {
        echo '<pre>User ID is MISSING from the database.</pre>';
    }

    mysqli_close($GLOBALS["___mysqli_ston"]);
}

?>