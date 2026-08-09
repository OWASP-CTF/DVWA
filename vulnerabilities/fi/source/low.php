<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Only allow one of the known, legitimate pages to be included. Anything
// else (absolute paths, traversal sequences, URLs/wrappers, ...) is rejected
// - the fix is an allowlist of exact filenames, not a blacklist of "bad"
// input patterns.
$allowedFiles = array( 'include.php', 'file1.php', 'file2.php', 'file3.php' );

if( !in_array( $file, $allowedFiles, true ) ) {
	echo "ERROR: File not found!";
	exit;
}

?>
