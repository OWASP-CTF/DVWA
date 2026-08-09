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

	// What does the file *actually* start with, regardless of what its name
	// or Content-Type claims? getimagesize() reads the real magic bytes/
	// header structure and reports the true image type in its 3rd element -
	// it doesn't need the (optional) exif extension for this.
	$image_info = @getimagesize( $uploaded_tmp );
	$real_type  = $image_info ? $image_info[2] : false;

	$ext_lower  = strtolower( $uploaded_ext );
	$ext_is_jpg = ( $ext_lower == "jpg" || $ext_lower == "jpeg" );
	$ext_is_png = ( $ext_lower == "png" );

	// The extension must be an image extension, the size must be sane, and
	// the *actual* type detected from the file's real bytes must genuinely
	// match that extension (a polyglot with a spoofed/second extension
	// won't line up).
	if( ( $ext_is_jpg || $ext_is_png ) &&
		( $uploaded_size < 100000 ) &&
		( ( $ext_is_jpg && $real_type == IMAGETYPE_JPEG ) || ( $ext_is_png && $real_type == IMAGETYPE_PNG ) ) ) {

		// Re-encode the image from the decoded pixel data rather than just
		// copying the uploaded bytes. This throws away anything appended
		// after the real image stream (e.g. a PHP payload smuggled inside
		// an otherwise-valid JPEG/PNG "polyglot"), so no extra bytes ever
		// reach disk.
		$img = ( $real_type == IMAGETYPE_JPEG ) ? @imagecreatefromjpeg( $uploaded_tmp ) : @imagecreatefrompng( $uploaded_tmp );

		if( $img === false ) {
			$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
		}
		else {
			$saved = ( $real_type == IMAGETYPE_JPEG ) ? imagejpeg( $img, $target_path, 100 ) : imagepng( $img, $target_path, 9 );
			imagedestroy( $img );

			if( !$saved ) {
				$html .= '<pre>Your image was not uploaded.</pre>';
			}
			else {
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
