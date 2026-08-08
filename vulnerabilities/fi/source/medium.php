<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Stripping "../" and the URL schemes was bypassable by nesting them
// ("....//" collapses back to "../"). Match the whole value against the set
// of pages this module actually serves instead.
$allowedPages = array( 'include.php', 'file1.php', 'file2.php', 'file3.php' );

if( !in_array( $file, $allowedPages, true ) ) {
	// This isn't the page we want!
	echo "ERROR: File not found!";
	exit;
}

?>
