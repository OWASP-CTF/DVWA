<?php

// Strict allowlist - the only pages this challenge is ever allowed to include.
// The key is what the user may ask for, the value is the fixed file name we use.
$allowed_pages = array(
	'include.php' => 'include.php',
	'file1.php'   => 'file1.php',
	'file2.php'   => 'file2.php',
	'file3.php'   => 'file3.php',
);

if( isset( $_GET[ 'page' ] ) ) {
	// The page we wish to display
	$page_requested = is_string( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : '';

	// Input validation: reject null bytes, traversal, absolute paths, stream
	// wrappers (php://, file://, data://, expect://, zip://, http(s)://) and
	// anything else that is not an exact match for an allowlisted page.
	if( strpos( $page_requested, "\0" ) !== false || !array_key_exists( $page_requested, $allowed_pages ) ) {
		// This isn't the page we want!
		echo "ERROR: File not found!";
		exit;
	}

	// Only ever hand a fixed, known-good file name to include() - never user input
	$file = $allowed_pages[ $page_requested ];
}

?>
