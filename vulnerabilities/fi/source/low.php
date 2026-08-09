<?php

// Get input
$file = $_GET[ 'page' ];

// Define explicit allowlist of safe pages
$allowed_pages = array(
    'include.php' => 'include.php',
    'file1.php'   => 'file1.php',
    'file2.php'   => 'file2.php',
    'file3.php'   => 'file3.php'
);

// Check if the requested page is in the allowlist
if( isset( $file ) && array_key_exists( $file, $allowed_pages ) ) {
    $file = $allowed_pages[ $file ];
} else {
    // Default fallback if an unauthorized or malicious file path is requested
    $file = 'include.php';
}

?>