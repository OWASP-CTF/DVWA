<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// File information
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1 ) );
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];
	$uploaded_info = is_uploaded_file( $uploaded_tmp ) ? @getimagesize( $uploaded_tmp ) : false;

	// Is it an image?
	if( ( $uploaded_ext == "jpg" || $uploaded_ext == "jpeg" || $uploaded_ext == "png" ) &&
		( $uploaded_size < 100000 ) &&
		( $uploaded_info !== false ) &&
		( $uploaded_info[ 2 ] == IMAGETYPE_JPEG || $uploaded_info[ 2 ] == IMAGETYPE_PNG ) ) {

		// Where are we going to be writing to? A random name plus the extension of the detected
		// image type means the uploaded name can never choose how the file is served.
		$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
		$target_path .= md5( uniqid() . $uploaded_name ) . ( ( $uploaded_info[ 2 ] == IMAGETYPE_PNG ) ? ".png" : ".jpg" );

		// Re-encode the image, destroying any code hidden inside it
		if( $uploaded_info[ 2 ] == IMAGETYPE_JPEG ) {
			$img   = @imagecreatefromjpeg( $uploaded_tmp );
			$saved = ( $img !== false ) && imagejpeg( $img, $target_path, 100 );
		}
		else {
			$img = @imagecreatefrompng( $uploaded_tmp );
			if( $img !== false ) {
				imagealphablending( $img, false );
				imagesavealpha( $img, true );
			}
			$saved = ( $img !== false ) && imagepng( $img, $target_path, 9 );
		}
		if( $img !== false )
			imagedestroy( $img );

		// Could we write the file to the upload folder?
		if( !$saved ) {
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
