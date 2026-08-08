<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Stripping "../" and "http://" substrings is trivially bypassed (double
// encoding, wrappers like php://, file://, etc). Whitelist instead.
$configFileNames = [
    'include.php',
    'file1.php',
    'file2.php',
    'file3.php',
];

if( !in_array($file, $configFileNames) ) {
    // This isn't the page we want!
    echo "ERROR: File not found!";
    exit;
}

?>
