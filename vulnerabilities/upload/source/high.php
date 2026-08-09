<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// Where are we going to be writing to?
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
	$target_path .= basename( $_FILES[ 'uploaded' ][ 'name' ] );

	// File information
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1);
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Is it an image?
	$imageInfo = getimagesize( $uploaded_tmp );
	if( ( strtolower( $uploaded_ext ) == "jpg" || strtolower( $uploaded_ext ) == "jpeg" || strtolower( $uploaded_ext ) == "png" ) &&
		( $uploaded_size < 100000 ) &&
		$imageInfo &&
		( $imageInfo[ 'mime' ] == 'image/jpeg' || $imageInfo[ 'mime' ] == 'image/png' ) ) {

		// getimagesize() only reads the header - it doesn't stop a polyglot file (a valid image
		// that also embeds executable PHP elsewhere in the file, e.g. via EXIF/comment fields)
		// from being saved verbatim, which could then be triggered by a separate file-inclusion
		// bug. Re-encoding via GD strips any embedded payload.
		if( $imageInfo[ 'mime' ] == 'image/jpeg' ) {
			$img = imagecreatefromjpeg( $uploaded_tmp );
			$saved = imagejpeg( $img, getcwd() . DIRECTORY_SEPARATOR . $target_path, 100 );
		}
		else {
			$img = imagecreatefrompng( $uploaded_tmp );
			$saved = imagepng( $img, getcwd() . DIRECTORY_SEPARATOR . $target_path, 9 );
		}
		imagedestroy( $img );

		if( $saved ) {
			// Yes!
			$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
		}
		else {
			// No
			$html .= '<pre>Your image was not uploaded.</pre>';
		}
	}
	else {
		// Invalid file
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
