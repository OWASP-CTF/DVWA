<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// File information
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1 ) );
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Is it an image?
	$uploaded_info = is_uploaded_file( $uploaded_tmp ) ? @getimagesize( $uploaded_tmp ) : false;
	if( ( $uploaded_ext == "jpg" || $uploaded_ext == "jpeg" || $uploaded_ext == "png" ) &&
		( $uploaded_size < 100000 ) &&
		( $uploaded_info !== false ) &&
		( $uploaded_info[2] == IMAGETYPE_JPEG || $uploaded_info[2] == IMAGETYPE_PNG ) ) {
		$target_path = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/" . bin2hex( random_bytes(16) ) . (($uploaded_info[2] == IMAGETYPE_PNG) ? '.png' : '.jpg');

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
