<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Only allow one of the known, legitimate pages to be included. The previous
// check (fnmatch("file*", $file) || $file == "include.php") is a weak
// allowlist that still matches attacker-supplied strings starting with
// "file" (e.g. "file:///etc/passwd" or "file.php/../../../../etc/passwd" on
// case-insensitive/legacy filesystems) or any wrapper containing "file*" as
// a prefix. An exact match against the known filenames closes that off.
$allowedFiles = array( 'include.php', 'file1.php', 'file2.php', 'file3.php' );

if( !in_array( $file, $allowedFiles, true ) ) {
	echo "ERROR: File not found!";
	exit;
}

?>
