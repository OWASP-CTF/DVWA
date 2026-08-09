<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
    // Get input and trim whitespace
    $target = trim($_REQUEST[ 'ip' ]);

    // SECURE FIX: Validate that the input is strictly an IP address
    if (filter_var($target, FILTER_VALIDATE_IP)) {
        // Determine OS and execute the ping command safely
        if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
            // Windows
            $cmd = shell_exec( 'ping ' . escapeshellarg($target) );
        }
        else {
            // *nix
            $cmd = shell_exec( 'ping -c 4 ' . escapeshellarg($target) );
        }

        // Output results
        echo "<pre>{$cmd}</pre>";
    } else {
        // Reject invalid input
        echo "<pre>ERROR: You must enter a valid IP address.</pre>";
    }
}

?>