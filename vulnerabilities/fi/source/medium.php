<?php

// Stripping "../" and "http://" substrings is trivially bypassed (double
// encoding, wrappers like php://, file://, etc). Whitelist instead.
$configFileNames = [
    'include.php',
    'file1.php',
    'file2.php',
    'file3.php',
];

// Leave $file unset when no page was requested at all, so index.php's own
// "no page yet" fallback (redirecting to the default page) still runs -
// only validate against the whitelist once a page was actually asked for.
if( isset( $_GET[ 'page' ] ) ) {
    // The page we wish to display
    $file = $_GET[ 'page' ];

    if( !in_array( $file, $configFileNames, true ) ) {
        // This isn't the page we want!
        echo "ERROR: File not found!";
        exit;
    }
}

?>
