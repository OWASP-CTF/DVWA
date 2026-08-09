<?php

// Is there any input?
if ( array_key_exists( "default", $_GET ) && !is_null ($_GET[ 'default' ]) ) {
	$default = $_GET['default'];
	
	# Allow only the supported language values.
	if (!in_array ($default, array ('English', 'French', 'Spanish', 'German'), true)) {
		header ("location: ?default=English");
		exit;
	}
}

?>
