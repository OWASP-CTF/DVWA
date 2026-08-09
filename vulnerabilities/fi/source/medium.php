<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Only allow one of the known, legitimate pages to be included. Stripping
// specific substrings like "http://" or "../" (as the previous blacklist
// did) is always bypassable (e.g. "....//", "htthttp://p://", mixed-case
// wrappers, absolute paths, PHP stream wrappers, ...). An exact allowlist of
// filenames has no such bypasses.
$allowedFiles = array( 'include.php', 'file1.php', 'file2.php', 'file3.php' );

if( !in_array( $file, $allowedFiles, true ) ) {
	echo "ERROR: File not found!";
	exit;
}

?>
