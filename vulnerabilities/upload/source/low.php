<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// File information
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1 ) );
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Where are we going to be writing to?
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
	$target_path .= basename( $uploaded_name );

	// Is it an image? Check both the extension and that the file content
	// actually decodes as an image - a client-supplied name/type is not
	// trustworthy on its own, so this can't be skipped.
	if( ( $uploaded_ext == "jpg" || $uploaded_ext == "jpeg" || $uploaded_ext == "png" ) &&
		( $uploaded_size < 100000 ) &&
		getimagesize( $uploaded_tmp ) !== false ) {

		// Can we move the file to the upload folder?
		if( !move_uploaded_file( $uploaded_tmp, $target_path ) ) {
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
