<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// File information
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];
	$image_info    = getimagesize( $uploaded_tmp );

	// Is it an image?
	if( $uploaded_size < 100000 && $image_info &&
		( $image_info[2] == IMAGETYPE_JPEG || $image_info[2] == IMAGETYPE_PNG ) ) {

		// Decode and re-encode the image so embedded content is not retained.
		if( $image_info[2] == IMAGETYPE_JPEG ) {
			$image = imagecreatefromjpeg( $uploaded_tmp );
			$uploaded_ext = 'jpg';
		}
		else {
			$image = imagecreatefrompng( $uploaded_tmp );
			$uploaded_ext = 'png';
		}

		$target_path  = DVWA_WEB_PAGE_TO_ROOT . 'hackable/uploads/';
		$target_path .= bin2hex( random_bytes( 16 ) ) . '.' . $uploaded_ext;
		$uploaded = $image && ( $uploaded_ext == 'jpg' ?
			imagejpeg( $image, $target_path, 100 ) :
			imagepng( $image, $target_path, 9 ) );

		if( $image ) {
			imagedestroy( $image );
		}

		if( !$uploaded ) {
			$html .= '<pre>Your image was not uploaded.</pre>';
		}
		else {
			$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
		}
	}
	else {
		// Invalid file
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
