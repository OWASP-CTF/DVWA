<?php

if( isset( $_POST[ 'Change' ] ) ) {
    // SECURE FIX: Enforce reCAPTCHA validation server-side
    $resp = recaptcha_check_answer(
        $_DVWA[ 'recaptcha_private_key' ],
        $_POST[ 'g-recaptcha-response' ]
    );

    if( $resp ) {
        // Validate and update password only if CAPTCHA check succeeded
        $pass_new = $_POST[ 'password_new' ];
        $pass_conf = $_POST[ 'password_conf' ];

        if( $pass_new === $pass_conf ) {
            // Use prepared statements to update password securely
            $pass_hash = md5( $pass_new );
            $user = dvwaCurrentUser();

            $query  = "UPDATE `users` SET password = ? WHERE user = ?;";
            $stmt   = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
            mysqli_stmt_bind_param($stmt, "ss", $pass_hash, $user);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            echo "<pre>Password changed.</pre>";
        } else {
            echo "<pre>Passwords did not match.</pre>";
        }
    } else {
        echo "<pre>reCAPTCHA was incorrect.</pre>";
    }
}

?>