<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Input validation - allow only known, exact file names (allow-list, not blacklist)
$configFileNames = [
    'include.php',
    'file1.php',
    'file2.php',
    'file3.php',
];

if( !in_array( $file, $configFileNames, true ) ) {
    // This isn't the page we want!
    echo "ERROR: File not found!";
    exit;
}

?>
