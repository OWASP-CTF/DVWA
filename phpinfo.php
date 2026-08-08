<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated') );

// phpinfo() dumps every loaded extension, ini setting and filesystem path -
// effectively a map of the host. Any authenticated user could previously
// reach this page; restrict it to the admin account.
if( dvwaCurrentUser() != 'admin' ) {
	http_response_code( 403 );
	print 'Unauthorised';
	exit;
}

phpinfo();

?>
