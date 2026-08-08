<?php

// The page we wish to display
// Only allow include.php or file{1..3}.php
$configFileNames = array(
	'include.php',
	'file1.php',
	'file2.php',
	'file3.php',
);

if( isset( $_GET[ 'page' ] ) ) {
	$file = $_GET[ 'page' ];

	if( !in_array( $file, $configFileNames, true ) ) {
		// This isn't the page we want!
		echo "ERROR: File not found!";
		exit;
	}
}

?>
