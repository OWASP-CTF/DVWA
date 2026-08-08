<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated') );

// phpinfo() lists the loaded extensions, every ini setting, the filesystem
// paths and the build flags. That is a map of the host, so it is restricted to
// the administrator rather than every authenticated user (A02:2025).
if( dvwaCurrentUser() != 'admin' ) {
	dvwaSecurityLog( 'phpinfo.denied' );
	http_response_code( 403 );
	print 'Unauthorised';
	exit;
}

phpinfo();

?>
