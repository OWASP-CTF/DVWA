<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ] = 'Blind SQL Injection Cookie Input' . $page[ 'title_separator' ].$page[ 'title' ];

if( isset( $_POST[ 'id' ] ) ) {
	$id = $_POST[ 'id' ];
	if( is_string( $id ) && preg_match( '/^\d+$/D', $id ) ) {
		setcookie( 'id', (string) (int) $id, [
			'path'     => '/vulnerabilities/sqli_blind/',
			'secure'   => false,
			'httponly' => true,
			'samesite' => 'Strict',
		] );
		$page[ 'body' ] .= "Cookie ID set!<br /><br /><br />";
		$page[ 'body' ] .= "<script>window.opener.location.reload(true);</script>";
	}
	else {
		http_response_code( 422 );
		$page[ 'body' ] .= "Invalid user ID.<br /><br /><br />";
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


