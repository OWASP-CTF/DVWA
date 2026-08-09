<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// Check Anti-CSRF token
	if (array_key_exists ("session_token", $_SESSION)) {
		$session_token = $_SESSION[ 'session_token' ];
	} else {
		$session_token = "";
	}
	checkToken( $_REQUEST[ 'user_token' ], $session_token, 'index.php' );

	// Where are we going to be writing to?
	$target_dir = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";

	// File information
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1) );
	$uploaded_type = $_FILES[ 'uploaded' ][ 'type' ];
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Is it an image? Check extension, declared MIME type, size, and that
	// it actually decodes as a real image.
	$image_info = getimagesize( $uploaded_tmp );
	if( ( $uploaded_ext == "jpg" || $uploaded_ext == "jpeg" || $uploaded_ext == "png" ) &&
		( $uploaded_type == "image/jpeg" || $uploaded_type == "image/png" ) &&
		( $uploaded_size < 100000 ) &&
		$image_info !== false ) {

		// The original name is never trusted for the path written to disk -
		// write under a fresh random name, keeping only the
		// already-validated extension, so nothing about the filename the
		// caller chose survives onto the filesystem.
		$target_file = bin2hex( random_bytes( 16 ) ) . '.' . $uploaded_ext;
		$target_path = $target_dir . $target_file;

		// Re-encoding through GD discards anything riding along in the
		// upload that isn't actual pixel data - the class of
		// polyglot-image-with-embedded-PHP upload that extension/MIME/
		// getimagesize checks alone do not catch, because all of them can
		// be true of a file that is simultaneously a valid image and a
		// valid PHP script. A real re-render can only ever produce pixels.
		$written = false;
		if( $image_info[2] === IMAGETYPE_JPEG ) {
			$img = @imagecreatefromjpeg( $uploaded_tmp );
			if( $img !== false ) {
				$written = imagejpeg( $img, getcwd() . DIRECTORY_SEPARATOR . $target_path, 100 );
				imagedestroy( $img );
			}
		}
		else if( $image_info[2] === IMAGETYPE_PNG ) {
			$img = @imagecreatefrompng( $uploaded_tmp );
			if( $img !== false ) {
				$written = imagepng( $img, getcwd() . DIRECTORY_SEPARATOR . $target_path, 9 );
				imagedestroy( $img );
			}
		}

		if( !$written ) {
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

// Generate Anti-CSRF token
generateSessionToken();

?>
