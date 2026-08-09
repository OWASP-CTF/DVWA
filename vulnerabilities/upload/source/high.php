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
	if( ( strtolower( $uploaded_ext ) == "jpg" || strtolower( $uploaded_ext ) == "jpeg" || strtolower( $uploaded_ext ) == "png" ) &&
		( $uploaded_size < 100000 ) &&
		( $uploaded_info = getimagesize( $uploaded_tmp ) ) ) {

		// Re-encode from decoded pixel data (by real detected type, not the claimed extension) to drop any payload smuggled in metadata/trailing bytes
		if( $uploaded_info[2] == IMAGETYPE_PNG )
			$img = imagecreatefrompng( $uploaded_tmp );
		elseif( $uploaded_info[2] == IMAGETYPE_JPEG )
			$img = imagecreatefromjpeg( $uploaded_tmp );
		else
			$img = false;

		if( $img === false ) {
			$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
		}
		else {
			$saved = ( $uploaded_info[2] == IMAGETYPE_PNG ) ? imagepng( $img, $target_path, 9 ) : imagejpeg( $img, $target_path, 100 );
			imagedestroy( $img );

			if( !$saved ) {
				// No
				$html .= '<pre>Your image was not uploaded.</pre>';
			}
			else {
				// Yes!
				$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
			}
		}
	}
	else {
		// Invalid file
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
