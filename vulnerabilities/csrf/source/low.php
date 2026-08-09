<?php

if( isset( $_GET[ 'Change' ] ) ) {
    // Anti-CSRF Protection: Check user token against session token
    if (array_key_exists ("session_token", $_SESSION)) {
        $session_token = $_SESSION[ 'session_token' ];
    } else {
        $session_token = "";
    }

    // Verify token or reject request
    checkToken( $_REQUEST[ 'user_token' ], $session_token, 'index.php' );

    // Get input
    $pass_new = $_GET[ 'password_new' ];
    $pass_conf = $_GET[ 'password_conf' ];

    // Do the passwords match?
    if( $pass_new == $pass_conf ) {
        // Use prepared statements to update password securely
        $pass_hash = md5( $pass_new );
        $user = $_SESSION['user'];

        $query  = "UPDATE `users` SET password = ? WHERE user = ?;";
        $stmt   = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
        mysqli_stmt_bind_param($stmt, "ss", $pass_hash, $user);
        mysqli_stmt_execute($stmt);

        // Feedback for the user
        echo "<pre>Password changed.</pre>";
    }
    else {
        // Feedback for the user
        echo "<pre>Passwords did not match.</pre>";
    }
}

// Re-generate session token for the next form submission
generateSessionToken();

?>