<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// Where are we going to be writing to?
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
	$target_path .= basename( $_FILES[ 'uploaded' ][ 'name' ] );

	// File information
	$uploaded_name = basename( $_FILES[ 'uploaded' ][ 'name' ] );
	$name_parts    = explode( '.', strtolower( $uploaded_name ) );
	$uploaded_ext  = array_pop( $name_parts );
	$uploaded_type = $_FILES[ 'uploaded' ][ 'type' ];
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];

	// Content-Type is client-supplied, so also allowlist the extension the webserver
	// dispatches on, with no interior php segment either (shell.php.jpg)
	if( ( $uploaded_type == "image/jpeg" || $uploaded_type == "image/png" ) &&
		in_array( $uploaded_ext, array( 'jpg', 'jpeg', 'png' ), true ) &&
		!array_intersect( $name_parts, array( 'php', 'phtml', 'phps', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8' ) ) &&
		( $uploaded_size < 100000 ) ) {

		// Can we move the file to the upload folder?
		if( !move_uploaded_file( $_FILES[ 'uploaded' ][ 'tmp_name' ], $target_path ) ) {
			// No
			$html .= '<pre>Your image was not uploaded.</pre>';
		}
		else {
			// Yes!
			$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
		}
	}
	else {
		// Invalid file
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
