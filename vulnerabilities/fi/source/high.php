<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// The old fnmatch( "file*" ) test matched far more than the intended pages --
// "file4.php", "file:///etc/passwd" and any path beginning "file" all passed.
// Compare against the exact set of pages this module serves.
$allowedPages = array( 'include.php', 'file1.php', 'file2.php', 'file3.php' );

if( !in_array( $file, $allowedPages, true ) ) {
	// This isn't the page we want!
	echo "ERROR: File not found!";
	exit;
}

?>
