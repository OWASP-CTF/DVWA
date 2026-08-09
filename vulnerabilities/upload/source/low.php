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
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Only allow real images: whitelist the extension, cap the size, and
	// make sure the file actually decodes as an image.
	$image_info = getimagesize( $uploaded_tmp );
	if( ( $uploaded_ext == "jpg" || $uploaded_ext == "jpeg" || $uploaded_ext == "png" ) &&
		( $uploaded_size < 100000 ) &&
		$image_info !== false ) {

		// The original name is never trusted for the path written to disk -
		// a name like "shell.php.jpg" would still carry a .php segment
		// through onto the filesystem. Write under a fresh random name
		// instead, keeping only the extension already validated above.
		$target_file = bin2hex( random_bytes( 16 ) ) . '.' . $uploaded_ext;
		$target_path = $target_dir . $target_file;

		// Passing the upload through GD's decoder and re-encoding it
		// discards anything riding along in the file that isn't actual
		// pixel data - metadata, appended bytes, a polyglot PHP payload -
		// none of which survives a real re-render the way it would survive
		// a plain copy of the uploaded bytes.
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
