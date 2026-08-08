<?php

// The real fix for this module is client side, in index.php: the option list is
// now built with createElement()/textContent instead of document.write(), so a
// payload in the query string can never become markup. This server side check
// is defence in depth.
//
// This level already had the allow list, but the payload could be carried
// in the URL fragment, which is never sent to the server. Only the client
// side change actually closes that.

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
