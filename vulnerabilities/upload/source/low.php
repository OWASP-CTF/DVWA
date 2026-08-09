<?php

if( isset( $_POST[ 'Upload' ] ) ) {
    // Where are we going to be writing to?
    $target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
    
    // Generate a random filename to prevent overwriting and prediction
    $random_name = bin2hex(random_bytes(16));
    
    // Get file extension and validate
    $uploaded_ext = strtolower(pathinfo($_FILES['uploaded']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
    
    // Validate extension
    if (!in_array($uploaded_ext, $allowed_extensions)) {
        $html .= '<pre>Your image was not uploaded. Invalid file type.</pre>';
    } else {
        // Construct target filename with random name
        $target_path .= $random_name . '.' . $uploaded_ext;
        
        // Validate MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($_FILES['uploaded']['tmp_name']);
        $allowed_mime = array('image/jpeg', 'image/png', 'image/gif');
        
        if (!in_array($mime_type, $allowed_mime)) {
            $html .= '<pre>Your image was not uploaded. Invalid MIME type.</pre>';
        } elseif ($_FILES['uploaded']['size'] > 100000) {
            $html .= '<pre>Your image was not uploaded. File too large.</pre>';
        } elseif (!move_uploaded_file($_FILES['uploaded']['tmp_name'], $target_path)) {
            $html .= '<pre>Your image was not uploaded.</pre>';
        } else {
            $html .= "<pre>{$target_path} successfully uploaded!</pre>";
        }
    }
}

?>
