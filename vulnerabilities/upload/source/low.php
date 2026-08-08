<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// Where are we going to be writing to?
	$target_path = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";

	// File information - the client supplied name, MIME type and size are all attacker controlled.
	$uploaded_name  = ( isset( $_FILES[ 'uploaded' ][ 'name' ] )     && is_string( $_FILES[ 'uploaded' ][ 'name' ] )     ) ? $_FILES[ 'uploaded' ][ 'name' ]     : '';
	$uploaded_tmp   = ( isset( $_FILES[ 'uploaded' ][ 'tmp_name' ] ) && is_string( $_FILES[ 'uploaded' ][ 'tmp_name' ] ) ) ? $_FILES[ 'uploaded' ][ 'tmp_name' ] : '';
	$uploaded_error = ( isset( $_FILES[ 'uploaded' ][ 'error' ] ) ) ? (int)$_FILES[ 'uploaded' ][ 'error' ] : UPLOAD_ERR_NO_FILE;

	// Strip any directory component and reject null bytes outright.
	$safe_name = ( strpos( $uploaded_name, "\0" ) === false ) ? basename( str_replace( '\\', '/', $uploaded_name ) ) : '';

	// The name must carry exactly ONE extension - 'shell.php.jpg' and friends are rejected here.
	$single_ext = ( preg_match( '/^[\w \-()]+\.[A-Za-z0-9]{1,5}\z/', $safe_name ) === 1 );

	// The REAL final extension (lower cased), taken from the sanitised name.
	$uploaded_ext = ( strrpos( $safe_name, '.' ) === false ) ? '' : strtolower( substr( $safe_name, strrpos( $safe_name, '.' ) + 1 ) );

	// Work out what the file actually IS by parsing its content, not by trusting the request.
	$image_info = ( $uploaded_error === UPLOAD_ERR_OK && $uploaded_tmp != '' && is_uploaded_file( $uploaded_tmp ) ) ? @getimagesize( $uploaded_tmp ) : false;
	$image_type = ( is_array( $image_info ) && isset( $image_info[ 'mime' ] ) ) ? $image_info[ 'mime' ] : '';

	// Allow-list: the real extension and the real content type both have to be an accepted image.
	$allowed = array(
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'gif'  => 'image/gif',
	);

	// Is it an image?
	if( $single_ext && isset( $allowed[ $uploaded_ext ] ) && $allowed[ $uploaded_ext ] === $image_type ) {

		// Server generated name with a fixed, safe extension - nothing from the request reaches the path.
		$target_ext  = ( $image_type == 'image/jpeg' ) ? 'jpg' : ( ( $image_type == 'image/png' ) ? 'png' : 'gif' );
		$target_file = bin2hex( random_bytes( 16 ) ) . '.' . $target_ext;
		$target_path = $target_path . $target_file;

		// Strip any appended payload/metadata by re-encoding the image through GD.
		$img   = false;
		$saved = false;
		if( $image_type == 'image/jpeg' && function_exists( 'imagecreatefromjpeg' ) ) {
			$img = @imagecreatefromjpeg( $uploaded_tmp );
			if( $img !== false )
				$saved = @imagejpeg( $img, $target_path, 100 );
		}
		elseif( $image_type == 'image/png' && function_exists( 'imagecreatefrompng' ) ) {
			$img = @imagecreatefrompng( $uploaded_tmp );
			if( $img !== false ) {
				imagealphablending( $img, false );
				imagesavealpha( $img, true );
				$saved = @imagepng( $img, $target_path, 9 );
			}
		}
		elseif( $image_type == 'image/gif' && function_exists( 'imagecreatefromgif' ) ) {
			$img = @imagecreatefromgif( $uploaded_tmp );
			if( $img !== false )
				$saved = @imagegif( $img, $target_path );
		}

		if( $img !== false )
			imagedestroy( $img );

		// Did the re-encoded image make it to the upload folder?
		if( !$saved ) {
			// No
			if( file_exists( $target_path ) )
				unlink( $target_path );

			$html .= '<pre>Your image was not uploaded.</pre>';
		}
		else {
			// Yes!
			$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
		}
	}
	else {
		// Invalid file
		$html .= '<pre>Your image was not uploaded.</pre>';
	}
}

?>
