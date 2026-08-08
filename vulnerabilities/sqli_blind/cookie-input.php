<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ] = 'Blind SQL Injection Cookie Input' . $page[ 'title_separator' ].$page[ 'title' ];

if( isset( $_POST[ 'id' ] ) ) {
	// Only ever write a number into the cookie. sqli_blind/source/high.php
	// validates it again on the way back in, because a cookie can be edited
	// in the browser without ever going through this page.
	if( is_numeric( $_POST[ 'id' ] ) ) {
		setcookie( 'id', (string) intval( $_POST[ 'id' ] ), 0, '/', '', dvwaIsHttps(), true );
		$page[ 'body' ] .= "Cookie ID set!<br /><br /><br />";
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
