<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// File information
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];
	$image_info    = getimagesize( $uploaded_tmp );

	// Is it an image?
	if( $image_info !== false &&
		( $image_info[ 2 ] == IMAGETYPE_JPEG || $image_info[ 2 ] == IMAGETYPE_PNG ) &&
		( $uploaded_size < 100000 ) ) {
		$uploaded_ext = ( $image_info[ 2 ] == IMAGETYPE_JPEG ) ? 'jpg' : 'png';
		$target_path  = DVWA_WEB_PAGE_TO_ROOT . 'hackable/uploads/';
		$target_path .= bin2hex( random_bytes( 16 ) ) . '.' . $uploaded_ext;

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
