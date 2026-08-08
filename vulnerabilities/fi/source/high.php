<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Input validation
// fnmatch("file*", $file) is bypassable - e.g. "file:///etc/passwd" also
// starts with "file", letting the file:// wrapper through. Whitelist the
// exact allowed filenames instead.
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
