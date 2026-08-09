<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// The uploaded file was previously moved into the web root with no validation at all -
	// any file (e.g. a .php web shell) could be uploaded and then executed by requesting it.
	// Only accept files that are genuinely a JPEG/PNG image, under a size limit, with a matching
	// MIME type, and re-encode them via GD to strip any embedded payload, matching
	// impossible.php's approach.
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1) );
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_type = $_FILES[ 'uploaded' ][ 'type' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Where are we going to be writing to?
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
	$target_path .= basename( $uploaded_name );

	if( ( $uploaded_ext == 'jpg' || $uploaded_ext == 'jpeg' || $uploaded_ext == 'png' ) &&
		( $uploaded_size < 100000 ) &&
		( $uploaded_type == 'image/jpeg' || $uploaded_type == 'image/png' ) &&
		getimagesize( $uploaded_tmp ) ) {

		// Strip any embedded payload by re-encoding the image
		if( $uploaded_type == 'image/jpeg' ) {
			$img = imagecreatefromjpeg( $uploaded_tmp );
			$saved = imagejpeg( $img, getcwd() . DIRECTORY_SEPARATOR . $target_path, 100 );
		}
		else {
			$img = imagecreatefrompng( $uploaded_tmp );
			$saved = imagepng( $img, getcwd() . DIRECTORY_SEPARATOR . $target_path, 9 );
		}
		imagedestroy( $img );

		if( $saved ) {
			// Yes!
			$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
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
