<?php

header ("X-XSS-Protection: 0");

// DECISIVE TEST: ignore user input entirely — a fixed, safe response.
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	$html .= '<pre>Hello CTFSAFEMARKER</pre>';
}

?>
