<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
    // Get input and trim whitespace
    $message = trim( $_POST[ 'mtxMessage' ] );
    $name    = trim( $_POST[ 'txtName' ] );

    // SECURE FIX: Encode special characters to prevent Stored XSS
    $message = htmlspecialchars( $message, ENT_QUOTES, 'UTF-8' );
    $name    = htmlspecialchars( $name, ENT_QUOTES, 'UTF-8' );

    // Prepare SQL query safely
    $query  = "INSERT INTO guestbook ( comment, name ) VALUES ( ?, ? );";
    $stmt   = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $message, $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

?>