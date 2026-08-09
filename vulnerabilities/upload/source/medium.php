<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// File information
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1) );
	$uploaded_type = $_FILES[ 'uploaded' ][ 'type' ];
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Where are we going to be writing to?
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
	$target_path .= basename( $uploaded_name );

	// $uploaded_type is a client-supplied HTTP header, trivially spoofed - it alone can never
	// prove the file's actual content. Also validate the extension and the file itself (via
	// getimagesize()), and re-encode it via GD to strip any embedded payload, matching
	// impossible.php's approach - a .php file with a forged image/jpeg Content-Type previously
	// passed straight through and was saved with its executable extension intact.
	if( ( $uploaded_ext == 'jpg' || $uploaded_ext == 'jpeg' || $uploaded_ext == 'png' ) &&
		( $uploaded_size < 100000 ) &&
		( $uploaded_type == "image/jpeg" || $uploaded_type == "image/png" ) &&
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
