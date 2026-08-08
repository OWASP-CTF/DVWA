<?php

if( isset( $_POST[ 'Upload' ] ) && isset( $_FILES[ 'uploaded' ] ) ) {

	$uploaded_size  = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp   = $_FILES[ 'uploaded' ][ 'tmp_name' ];
	$uploaded_error = $_FILES[ 'uploaded' ][ 'error' ];

	// Where are we going to be writing to?
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";

	// High level used to trust a client-controlled extension
	// (".jpg"/".jpeg"/".png", taken from the last "." in the supplied
	// name - defeated by "shell.jpg.php" or a null byte, since the
	// extension it read was never what the file was actually saved as)
	// plus a bare getimagesize() check, which only inspects the file's
	// header. getimagesize() alone happily passes a real JPEG/PNG that
	// also has PHP appended after the image data or hidden in a JPEG
	// comment / EXIF field - exactly the payload this level's exploit
	// relies on chaining with a file-inclusion bug elsewhere. We now
	// ignore the client-supplied name/extension entirely and re-encode
	// the image via GD (as impossible.php does), which only ever writes
	// out the decoded pixel data - any smuggled PHP is discarded.
	$image_info = ( $uploaded_error === UPLOAD_ERR_OK && is_uploaded_file( $uploaded_tmp ) )
		? @getimagesize( $uploaded_tmp )
		: false;

	if( $image_info !== false &&
		( $image_info[ 2 ] === IMAGETYPE_JPEG || $image_info[ 2 ] === IMAGETYPE_PNG ) &&
		( $uploaded_size < 100000 ) ) {

		if( $image_info[ 2 ] === IMAGETYPE_JPEG ) {
			$img      = @imagecreatefromjpeg( $uploaded_tmp );
			$safe_ext = 'jpg';
		}
		else {
			$img      = @imagecreatefrompng( $uploaded_tmp );
			$safe_ext = 'png';
		}

		if( $img !== false ) {
			$target_path .= bin2hex( random_bytes( 16 ) ) . '.' . $safe_ext;
			$written = ( $safe_ext == 'jpg' ) ? imagejpeg( $img, $target_path, 100 ) : imagepng( $img, $target_path, 9 );
			imagedestroy( $img );

			if( $written ) {
				// Yes!
				$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
			}
			else {
				// No
				$html .= '<pre>Your image was not uploaded.</pre>';
			}
		}
		else {
			// GD could not decode it as a genuine image
			$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
		}
	}
	else {
		// Invalid file
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
