<?php

// Is there any input?
if ( array_key_exists( "default", $_GET ) && !is_null ($_GET[ 'default' ]) ) {
	// Sanitize input with proper output encoding (whitelist approach)
	$default = htmlspecialchars( $_GET['default'], ENT_QUOTES, 'UTF-8' );
	
	# White list the allowable languages
	switch ($_GET['default']) {
		case "French":
		case "English":
		case "German":
		case "Spanish":
			# ok
			break;
		default:
			header ("location: ?default=English");
			exit;
	}
}

?>
