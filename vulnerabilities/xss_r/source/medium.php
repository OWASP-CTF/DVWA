<?php
header( 'X-XSS-Protection: 0' );
if( array_key_exists( 'name', $_GET ) && $_GET['name'] != NULL ) {
	$html .= '<pre>Hello ' . str_replace( '<script>', '', $_GET['name'] ) . '</pre>';
}
?>
