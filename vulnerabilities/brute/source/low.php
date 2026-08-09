<?php

if( isset( $_GET[ 'Login' ] ) ) {
    // 1. Rate Limiting / Delay Defense
    // Track failed attempts in session
    if (!isset($_SESSION['failed_login_attempts'])) {
        $_SESSION['failed_login_attempts'] = 0;
        $_SESSION['last_login_attempt'] = time();
    }

    // Enforce a time delay if attempts exceed threshold
    if ($_SESSION['failed_login_attempts'] >= 3) {
        $time_since_last = time() - $_SESSION['last_login_attempt'];
        if ($time_since_last < 2) { // Require at least 2 seconds between attempts
            sleep(2); // Introduce delay to slow down automated scripts
        }
    }

    $_SESSION['last_login_attempt'] = time();

    // 2. Input Sanitization & Secure Query Execution
    $user = $_GET[ 'user' ];
    $pass = $_GET[ 'pass' ];
    $pass_hash = md5( $pass );

    // Use prepared statements to protect against SQLi while handling brute force
    $query  = "SELECT * FROM `users` WHERE user = ? AND password = ?;";
    $stmt   = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
    mysqli_stmt_bind_param($stmt, "ss", $user, $pass_hash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if( $result && mysqli_num_rows( $result ) == 1 ) {
        // Reset counter on successful login
        $_SESSION['failed_login_attempts'] = 0;
        echo "<p>Welcome to the password protected area {$user}</p>";
    } else {
        // Increment failure counter
        $_SESSION['failed_login_attempts']++;
        
        // Additional sleep defense on failed attempt
        sleep(1);
        echo "<pre><br />Username and/or password incorrect.</pre>";
    }
}

?>