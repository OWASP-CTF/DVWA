<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Input validation
// fnmatch("file*") also matches wrapper/traversal payloads starting with "file"; use an exact allowlist
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
