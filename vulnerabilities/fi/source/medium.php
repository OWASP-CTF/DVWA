<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Input validation
$file = str_replace( array( "http://", "https://" ), "", $file );
$file = str_replace( array( "../", "..\\" ), "", $file );

// Blocklists are bypassable (e.g. nested traversal); enforce an allowlist instead
$configFileNames = [
	'include.php',
	'file1.php',
	'file2.php',
	'file3.php',
];

if( isset( $_GET[ 'page' ] ) && !in_array( $file, $configFileNames ) ) {
	// This isn't the page we want!
	echo "ERROR: File not found!";
	exit;
}

?>
