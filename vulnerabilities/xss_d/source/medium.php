<?php

// Is there any input?
if ( array_key_exists( "default", $_GET ) && !is_null ($_GET[ 'default' ]) ) {
	// Sanitize input with proper output encoding (not just script tag detection)
	$default = htmlspecialchars( $_GET['default'], ENT_QUOTES, 'UTF-8' );
}

?>
