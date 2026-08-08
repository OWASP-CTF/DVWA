<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// Where are we going to be writing to?
	$target_dir = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";

	// File information
	$uploaded_name = basename( $_FILES[ 'uploaded' ][ 'name' ] );
	$uploaded_ext  = strtolower( pathinfo( $uploaded_name, PATHINFO_EXTENSION ) );
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Allow list of image types. What the file actually is decides, not the
	// client supplied name or Content-Type header.
	$allowed_types = array( 'jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG, 'png' => IMAGETYPE_PNG );

	$image_info = ( is_uploaded_file( $uploaded_tmp ) ? @getimagesize( $uploaded_tmp ) : false );

	// Is it an image?
	if( isset( $allowed_types[ $uploaded_ext ] ) &&
		( $uploaded_size < 100000 ) &&
		( $image_info !== false ) &&
		( $image_info[2] === $allowed_types[ $uploaded_ext ] ) ) {

		// Build a safe name: no directory parts, and exactly one extension so
		// nothing can be smuggled through as file.php.jpg.
		$base_name = preg_replace( '/[^A-Za-z0-9_-]/', '_', pathinfo( $uploaded_name, PATHINFO_FILENAME ) );
		if( $base_name === '' ) {
			$base_name = bin2hex( random_bytes( 8 ) );
		}
		$target_file = $base_name . '.' . $uploaded_ext;
		$target_path = $target_dir . $target_file;

		// Re-encode the image so anything hidden inside it (metadata, appended
		// script) is discarded rather than written to the web root.
		$temp_file  = ( ( ini_get( 'upload_tmp_dir' ) == '' ) ? ( sys_get_temp_dir() ) : ( ini_get( 'upload_tmp_dir' ) ) );
		$temp_file .= DIRECTORY_SEPARATOR . bin2hex( random_bytes( 16 ) ) . '.' . $uploaded_ext;

		$written = false;
		if( $image_info[2] === IMAGETYPE_JPEG ) {
			$img = @imagecreatefromjpeg( $uploaded_tmp );
			if( $img !== false ) {
				$written = imagejpeg( $img, $temp_file, 100 );
				imagedestroy( $img );
			}
		}
		else {
			$img = @imagecreatefrompng( $uploaded_tmp );
			if( $img !== false ) {
				$written = imagepng( $img, $temp_file, 9 );
				imagedestroy( $img );
			}
		}

		// Can we move the file to the upload folder?
		if( !( $written && rename( $temp_file, getcwd() . DIRECTORY_SEPARATOR . $target_path ) ) ) {
			// No
			$html .= '<pre>Your image was not uploaded.</pre>';
		}
		else {
			// Yes!
			$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
		}

		// Delete any temp files
		if( file_exists( $temp_file ) )
			unlink( $temp_file );
	}
	else {
		// Invalid file
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
