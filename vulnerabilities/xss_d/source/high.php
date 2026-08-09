<?php

// Is there any input?
if ( array_key_exists( "default", $_GET ) && !is_null ($_GET[ 'default' ]) ) {

	# The page's JavaScript takes everything after the first "default=" to the end of the
	# URL, so the rest of the query string has to be checked, not just this parameter.
	$query = array_key_exists ("QUERY_STRING", $_SERVER) ? $_SERVER['QUERY_STRING'] : "";
	$offset = strpos ($query, "default=");
	$selected = $offset === false ? "" : rawurldecode (substr ($query, $offset + 8));

	# White list the allowable languages
	switch ($selected) {
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
