<?php

// The page we wish to display. When no page is requested $file is left unset
// so index.php can redirect to the default, which is what the menu link relies
// on.
$file = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : null;

// fnmatch('file*', ...) matched the file:// wrapper, so
// 'file:///etc/passwd' satisfied it.
//
// Only these four pages are ever included. An allow list of exact names is the
// only check that cannot be walked around with encoding tricks, wrappers such
// as file:// or php://, or a traversal sequence.
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
