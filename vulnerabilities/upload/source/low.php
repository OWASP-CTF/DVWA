<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// Where are we going to be writing to?
	$target_path = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
	$uploaded_tmp = $_FILES['uploaded']['tmp_name'];
	$mime = (new finfo(FILEINFO_MIME_TYPE))->file($uploaded_tmp);
	$extension = array('image/jpeg' => 'jpg', 'image/png' => 'png')[$mime] ?? null;
	if ($extension === null || !getimagesize($uploaded_tmp)) {
		$html .= '<pre>Your image was not uploaded.</pre>';
		exit;
	}
	$target_path .= bin2hex(random_bytes(16)) . '.' . $extension;

	// Can we move the file to the upload folder?
	if( !move_uploaded_file( $_FILES[ 'uploaded' ][ 'tmp_name' ], $target_path ) ) {
		// No
		$html .= '<pre>Your image was not uploaded.</pre>';
	}
	else {
		// Yes!
		$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
	}
}

?>
