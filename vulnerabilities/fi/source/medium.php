<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// A single-pass str_replace on "../" doesn't catch nested traversal (e.g. "....//" becomes
// "../" after one removal pass), doesn't stop absolute paths or PHP stream wrappers
// (php://filter, etc), and only strips a few known-bad substrings rather than actually
// restricting what can be included. Only allow include.php or file{1..3}.php instead.
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
