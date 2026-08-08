<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ] = 'Blind SQL Injection Cookie Input' . $page[ 'title_separator' ].$page[ 'title' ];

if( isset( $_POST[ 'id' ] ) ) {
	// Check Anti-CSRF token. Without this, any page a logged-in victim has
	// open elsewhere could silently POST here on their behalf and change
	// the cookie ID the blind-SQLi lesson trusts, with no confirmation.
	if (array_key_exists ("session_token", $_SESSION)) {
		$session_token = $_SESSION[ 'session_token' ];
	} else {
		$session_token = "";
	}
	checkToken( $_REQUEST[ 'user_token' ], $session_token, 'cookie-input.php' );

	$id = $_POST[ 'id' ];

	// Only a plain non-negative integer is ever a legitimate user ID.
	if( is_string( $id ) && preg_match( '/^\d+$/D', $id ) ) {
		setcookie( 'id', (string) (int) $id );
		$page[ 'body' ] .= "Cookie ID set!<br /><br /><br />";
		$page[ 'body' ] .= "<script>window.opener.location.reload(true);</script>";
	} else {
		http_response_code( 422 );
		$page[ 'body' ] .= "<pre>ERROR: ID must be a positive integer.</pre>";
	}
}

// Generate a fresh Anti-CSRF token for the form rendered below.
generateSessionToken();

$page[ 'body' ] .= "
<form action=\"#\" method=\"POST\">
	<input type=\"text\" size=\"15\" name=\"id\">
	<input type=\"submit\" name=\"Submit\" value=\"Submit\">
	" . tokenField() . "
</form>
<hr />
<br />

<button onclick=\"self.close();\">Close</button>";

dvwaSourceHtmlEcho( $page );

?>


