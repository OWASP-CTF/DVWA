<?php

// No page requested. Leave $file unset so index.php can send the browser to
// the module's landing page, exactly as it does for the unhardened levels.
// Erroring out here made /vulnerabilities/fi/ unreachable.
if( !array_key_exists( 'page', $_GET ) ) {
	return;
}

// The page we wish to display
$file = $_GET[ 'page' ];

// Only allow include.php or file{1..3}.php
$configFileNames = [
    'include.php',
    'file1.php',
    'file2.php',
    'file3.php',
];

if( !is_string($file) || !in_array($file, $configFileNames, true) ) {
    // This isn't the page we want!
    echo "ERROR: File not found!";
    exit;
}

?>
