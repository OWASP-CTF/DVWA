<?php

// The token the page's JavaScript builds comes from public, static data, so the page
// also has to hand back a per-render secret that only this session was given.
function javascriptIssueToken() {
	$_SESSION[ 'javascript_token' ] = bin2hex( random_bytes( 16 ) );

	return $_SESSION[ 'javascript_token' ];
}

function javascriptCheckToken( $token, $js_token ) {
	if( !is_string( $token ) || !is_string( $js_token ) || !isset( $_SESSION[ 'javascript_token' ] ) ) {
		return false;
	}

	if( !hash_equals( $_SESSION[ 'javascript_token' ], $js_token ) ) {
		return false;
	}

	return hash_equals( hash( "sha256", hash( "sha256", "XX" . strrev( "success" ) ) . "ZZ" ), $token );
}

$javascriptSource .= '<script src="' . DVWA_WEB_PAGE_TO_ROOT . 'vulnerabilities/javascript/source/high.js"></script>';
?>
