<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	if( !isset( $_FILES[ 'uploaded' ] ) || $_FILES[ 'uploaded' ][ 'error' ] !== UPLOAD_ERR_OK ) {
		$html .= '<pre>Your image was not uploaded.</pre>';
	}
	else {
		// File information
		$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
		$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1) );
		$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
		$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

		// Where are we going to be writing to?
		$target_path   = DVWA_WEB_PAGE_TO_ROOT . 'hackable/uploads/';

		// This level performed no checks at all and wrote the attacker's file name
		// straight under the web root, which is a direct path to RCE.
		//
		// The decoder is chosen from what the bytes actually are, never from the
		// client supplied Content-Type, and the extension has to agree with it.
		// Trusting the declared type meant a real PNG sent as image/jpeg reached
		// imagecreatefromjpeg(), which returns false and then fatals inside
		// imagejpeg().
		$image_info = getimagesize( $uploaded_tmp );
		$image_type = ( $image_info !== false ) ? $image_info[2] : null;

		$type_matches_extension =
			( $image_type === IMAGETYPE_JPEG && ( $uploaded_ext === 'jpg' || $uploaded_ext === 'jpeg' ) ) ||
			( $image_type === IMAGETYPE_PNG  && $uploaded_ext === 'png' );

		if( !$type_matches_extension || $uploaded_size >= 100000 ) {
			// Invalid file
			$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
		}
		else {
			// The stored name is generated server side, so the client cannot
			// choose the extension or smuggle a second one past the check.
			$random_name = bin2hex( random_bytes(16) ) . '.' . $uploaded_ext;
			$target_file = $random_name;
			$temp_file   = ( ( ini_get( 'upload_tmp_dir' ) == '' ) ? ( sys_get_temp_dir() ) : ( ini_get( 'upload_tmp_dir' ) ) );
			$temp_file  .= DIRECTORY_SEPARATOR . $random_name;

			// Re-encode the image. This is what actually removes PHP smuggled
			// into EXIF or appended after the image data: only the decoded
			// pixels survive, everything else is dropped.
			$img = ( $image_type === IMAGETYPE_JPEG )
				? imagecreatefromjpeg( $uploaded_tmp )
				: imagecreatefrompng( $uploaded_tmp );

			if( $img === false ) {
				error_log( 'dvwa upload: could not decode an image that passed getimagesize()' );
				$html .= '<pre>Your image was not uploaded.</pre>';
			}
			else {
				if( $image_type === IMAGETYPE_JPEG ) {
					imagejpeg( $img, $temp_file, 100 );
				}
				else {
					imagepng( $img, $temp_file, 9 );
				}
				imagedestroy( $img );

				// Can we move the file to the web root from the temp folder?
				if( rename( $temp_file, ( getcwd() . DIRECTORY_SEPARATOR . $target_path . $target_file ) ) ) {
					// Yes!
					$safe_file = htmlspecialchars( $target_file, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
					$html .= "<pre><a href='{$target_path}{$safe_file}'>{$safe_file}</a> succesfully uploaded!</pre>";
				}
				else {
					// No
					$html .= '<pre>Your image was not uploaded.</pre>';
				}

				// Delete any temp files
				if( file_exists( $temp_file ) )
					unlink( $temp_file );
			}
		}
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
