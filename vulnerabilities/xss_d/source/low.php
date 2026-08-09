<?php

if( array_key_exists( 'default', $_GET ) && !is_null( $_GET[ 'default' ] ) ) {
	$allowedLanguages = [
		'English',
		'French',
		'Spanish',
		'German',
	];

	if( !in_array( $_GET[ 'default' ], $allowedLanguages, true ) ) {
		header( 'Location: ?default=English' );
		exit;
	}
}

?>
