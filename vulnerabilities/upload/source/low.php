<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	$uploaded_file = $_FILES[ 'uploaded' ] ?? null;
	$uploaded_tmp  = $uploaded_file[ 'tmp_name' ] ?? '';
	$uploaded_size = $uploaded_file[ 'size' ] ?? 0;
	$image_info    = $uploaded_tmp !== '' ? @getimagesize( $uploaded_tmp ) : false;
	$image_type    = $image_info !== false ? $image_info[ 2 ] : null;
	$allowed_types = array(
		IMAGETYPE_JPEG => array( 'extension' => 'jpg', 'create' => 'imagecreatefromjpeg', 'write' => 'imagejpeg' ),
		IMAGETYPE_PNG  => array( 'extension' => 'png', 'create' => 'imagecreatefrompng', 'write' => 'imagepng' ),
	);

	if( $uploaded_file !== null &&
		( $uploaded_file[ 'error' ] ?? UPLOAD_ERR_NO_FILE ) === UPLOAD_ERR_OK &&
		$uploaded_size > 0 && $uploaded_size < 100000 &&
		isset( $allowed_types[ $image_type ] ) ) {
		$type_config = $allowed_types[ $image_type ];
		$image       = @$type_config[ 'create' ]( $uploaded_tmp );
		$temp_file   = tempnam( sys_get_temp_dir(), 'dvwa_upload_' );

		if( $image !== false && $temp_file !== false && @$type_config[ 'write' ]( $image, $temp_file ) ) {
			$target_path = DVWA_WEB_PAGE_TO_ROOT . 'hackable/uploads/';
			$target_file = bin2hex( random_bytes( 16 ) ) . '.' . $type_config[ 'extension' ];

			if( rename( $temp_file, getcwd() . DIRECTORY_SEPARATOR . $target_path . $target_file ) ) {
				$html .= "<pre>{$target_path}{$target_file} succesfully uploaded!</pre>";
			}
			else {
				$html .= '<pre>Your image was not uploaded.</pre>';
			}
		}
		else {
			$html .= '<pre>Your image was not uploaded.</pre>';
		}

		if( is_resource( $image ) || $image instanceof GdImage )
			imagedestroy( $image );
		if( $temp_file !== false && file_exists( $temp_file ) )
			unlink( $temp_file );
	}
	else {
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
