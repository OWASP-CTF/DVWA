<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Only allow the pages provided by this challenge
$allowedFiles = array(
	"include.php",
	"file1.php",
	"file2.php",
	"file3.php",
);

if( !in_array( $file, $allowedFiles, true ) ) {
	// This isn't the page we want!
	echo "ERROR: File not found!";
	exit;
}

?>
