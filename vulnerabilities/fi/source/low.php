<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Only allow the built-in challenge pages.
$allowedFileNames = [
	'include.php',
	'file1.php',
	'file2.php',
	'file3.php',
];

if( !in_array( $file, $allowedFileNames, true ) ) {
	echo "ERROR: File not found!";
	exit;
}

?>
