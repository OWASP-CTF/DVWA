<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// File information
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// getimagesize() alone only proves the file *starts* with image data, so
	// "shell.php.jpg" or a GIF-header polyglot still slipped through. Derive
	// the type from the contents and rebuild the file from scratch below.
	$image_info = @getimagesize( $uploaded_tmp );
	$image_type = ( $image_info === false ) ? null : $image_info[2];

	$allowed_types = array(
		IMAGETYPE_JPEG => 'jpg',
		IMAGETYPE_PNG  => 'png',
	);

	if( isset( $allowed_types[ $image_type ] ) && $uploaded_size < 100000 ) {
		// The stored name is generated server side, so the uploader can never
		// choose the extension or traverse out of the uploads directory.
		$target_path  = DVWA_WEB_PAGE_TO_ROOT . 'hackable/uploads/';
		$target_path .= bin2hex( random_bytes( 16 ) ) . '.' . $allowed_types[ $image_type ];

		// Re-encode the image so that any script smuggled into the metadata,
		// the pixel data or the trailing bytes is discarded.
		if( $image_type == IMAGETYPE_JPEG ) {
			$img   = @imagecreatefromjpeg( $uploaded_tmp );
			$saved = ( $img !== false ) && imagejpeg( $img, $target_path, 100 );
		}
		else {
			$img   = @imagecreatefrompng( $uploaded_tmp );
			$saved = ( $img !== false ) && imagepng( $img, $target_path, 9 );
		}

		if( $img !== false ) {
			imagedestroy( $img );
		}

		if( $saved ) {
			// Yes!
			$html .= "<pre>" . htmlspecialchars( $target_path, ENT_QUOTES, 'UTF-8' ) . " succesfully uploaded!</pre>";
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
