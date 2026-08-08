<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	$uploaded_tmp = $_FILES[ 'uploaded' ][ 'tmp_name' ];
	$image_info = getimagesize( $uploaded_tmp );
	$extension = $image_info ? image_type_to_extension( $image_info[2], false ) : false;
	if( !in_array( $extension, array( 'jpeg', 'png' ), true ) || $_FILES[ 'uploaded' ][ 'size' ] >= 100000 ) {
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
		return;
	}
	$target_path = DVWA_WEB_PAGE_TO_ROOT . 'hackable/uploads/' . bin2hex( random_bytes( 16 ) ) . '.' . $extension;

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

?>
