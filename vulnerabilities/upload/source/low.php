<?php

if( isset( $_POST[ 'Upload' ] ) ) {
    // Target directory
    $target_path = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
    
    // File information
    $uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
    $uploaded_ext  = strtolower( pathinfo( $uploaded_name, PATHINFO_EXTENSION ) );
    $uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
    $uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

    // Define allowed extensions and MIME types
    $allowed_exts  = array( 'jpg', 'jpeg', 'png' );
    
    // Validate extension, file size (< 100KB), and image dimensions
    if( in_array( $uploaded_ext, $allowed_exts ) && 
        ( $uploaded_size < 100000 ) && 
        @getimagesize( $uploaded_tmp ) ) {

        // Construct safe target filename
        $target_file = $target_path . basename( $uploaded_name );

        // Move the file
        if( move_uploaded_file( $uploaded_tmp, $target_file ) ) {
            echo "<pre>{$target_file} succesfully uploaded!</pre>";
        } else {
            echo "<pre>Your image was not uploaded.</pre>";
        }
    } else {
        echo "<pre>Your image was not uploaded. Only JPG/PNG images under 100KB are allowed.</pre>";
    }
}

?>