<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Only allow include.php or file{1..3}.php
$configFileNames = [
    'include.php',
    'file1.php',
    'file2.php',
    'file3.php',
];

if( !is_string( $file ) || !in_array( $file, $configFileNames, true ) ) {
    // This isn't the page we want!
    echo "ERROR: File not found!";
    exit;
}

?>
