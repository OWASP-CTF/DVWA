<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Input validation - allow only known, exact file names (allow-list, not blacklist).
// (A wildcard/prefix match such as fnmatch("file*", $file) is unsafe: it also matches
// wrapper schemes like "file://" and "file4.php", so we compare exact strings instead.)
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
