<?php

// The real fix for this module is client side, in index.php: the option list is
// now built with createElement()/textContent instead of document.write(), so a
// payload in the query string can never become markup. This server side check
// is defence in depth.
//
// A 'does it contain <script' check was bypassable with any other tag or
// event handler, so it has been replaced by the same allow list.

if ( array_key_exists( "default", $_GET ) && !is_null ($_GET[ 'default' ]) ) {

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
