<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// The module only ever links to these pages. Anything else -- a traversal
// sequence, an absolute path, a php:// wrapper or a remote URL -- is refused
// outright rather than filtered.
$allowedPages = array( 'include.php', 'file1.php', 'file2.php', 'file3.php' );

if( !in_array( $file, $allowedPages, true ) ) {
	// This isn't the page we want!
	echo "ERROR: File not found!";
	exit;
}

?>
