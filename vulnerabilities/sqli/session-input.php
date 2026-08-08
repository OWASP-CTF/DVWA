<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ] = 'SQL Injection Session Input' . $page[ 'title_separator' ].$page[ 'title' ];

if( isset( $_POST[ 'id' ] ) ) {
	// Only ever store a number in the session. Validating where the value
	// enters the application stops it being a second order injection source
	// for sqli/source/high.php.
	if( is_numeric( $_POST[ 'id' ] ) ) {
		$_SESSION[ 'id' ] = intval( $_POST[ 'id' ] );
		$page[ 'body' ] .= "Session ID: " . htmlspecialchars( (string) $_SESSION[ 'id' ], ENT_QUOTES, 'UTF-8' ) . "<br /><br /><br />";
		$page[ 'body' ] .= "<script>window.opener.location.reload(true);</script>";
	}
	else {
		$page[ 'body' ] .= "ID must be a number.<br /><br /><br />";
	}
}

$page[ 'body' ] .= "
<form action=\"#\" method=\"POST\">
	<input type=\"text\" size=\"15\" name=\"id\">
	<input type=\"submit\" name=\"Submit\" value=\"Submit\">
</form>
<hr />
<br />

<button onclick=\"self.close();\">Close</button>";

dvwaSourceHtmlEcho( $page );

?>
