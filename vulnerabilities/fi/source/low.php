<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// PATCH (CTF): whitelist the intended pages; reject path traversal / absolute paths.
if( $file != "include.php" && $file != "file1.php" && $file != "file2.php" && $file != "file3.php" ) {
	echo "ERROR: File not found!";
	exit;
}

?>
