<?php

// Is there a default language?
if ( !array_key_exists( "default", $_GET ) || $_GET[ 'default' ] == NULL ) {
    header( "location: ?default=English" );
    exit;
}

// SECURE FIX: Prevent DOM XSS by sanitizing/decoding and escaping output
$default = $_GET['default'];

// Check for malicious characters like script tags or quotes
if (preg_match("/<script/i", $default) || preg_match("/[<>\'\"]/i", $default)) {
    header("location: ?default=English");
    exit;
}

?>