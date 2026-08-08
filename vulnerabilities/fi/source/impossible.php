<?php

// The page we wish to display. When no page is requested $file is left unset
// so index.php can redirect to the default, which is what the menu link relies
// on.
$file = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : null;

// Reference implementation: an allow list of exact names, compared strictly so
// a value such as 0 cannot match by type juggling.
$configFileNames = [
    'include.php',
    'file1.php',
    'file2.php',
    'file3.php',
];

if( $file === null ) {
    // No page requested, let index.php send the browser to the default.
    unset( $file );
}
elseif( !in_array($file, $configFileNames, true) ) {
    // This isn't the page we want!
    echo "ERROR: File not found!";
    exit;
}

?>
