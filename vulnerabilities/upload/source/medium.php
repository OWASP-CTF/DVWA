<?php

if( isset( $_POST[ 'Upload' ] ) && isset( $_FILES[ 'uploaded' ] ) ) {

	$uploaded_size  = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp   = $_FILES[ 'uploaded' ][ 'tmp_name' ];
	$uploaded_error = $_FILES[ 'uploaded' ][ 'error' ];

	// Where are we going to be writing to?
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";

	// Medium level used to trust the client-supplied Content-Type
	// ($_FILES['uploaded']['type']) - a header the browser sets from
	// whatever the attacker's request claims it to be, so a web shell
	// sent with "Content-Type: image/jpeg" sailed straight through and
	// was saved under its original (executable) name. We no longer look
	// at the reported name, extension or Content-Type at all; the file
	// type is determined only from the actual bytes on disk.
	$image_info = ( $uploaded_error === UPLOAD_ERR_OK && is_uploaded_file( $uploaded_tmp ) )
		? @getimagesize( $uploaded_tmp )
		: false;

	if( $image_info !== false &&
		( $image_info[ 2 ] === IMAGETYPE_JPEG || $image_info[ 2 ] === IMAGETYPE_PNG ) &&
		( $uploaded_size < 100000 ) ) {

		// Re-encode via GD (as impossible.php does), so anything that
		// isn't genuine pixel data - a web shell appended after the
		// image data, hidden in a JPEG comment, in EXIF metadata, etc -
		// is discarded rather than written to disk. The extension we
		// save with is derived only from the verified image type, so a
		// name like "shell.php.jpg", "shell.jpg.php" or a null-byte
		// trick in the filename can no longer influence what gets saved.
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
