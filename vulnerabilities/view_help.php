<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ] = 'Help' . $page[ 'title_separator' ].$page[ 'title' ];

if (array_key_exists ("id", $_GET) &&
	array_key_exists ("security", $_GET) &&
	array_key_exists ("locale", $_GET)) {
	$id = $_GET[ 'id' ];
	$locale = $_GET[ 'locale' ];
	$helpModules = array( 'api', 'authbypass', 'bac', 'brute', 'captcha', 'cryptography', 'csp', 'csrf', 'exec', 'fi', 'javascript', 'open_redirect', 'sqli', 'sqli_blind', 'upload', 'weak_id', 'xss_d', 'xss_r', 'xss_s' );

	if (in_array($id, $helpModules, true) && in_array($locale, array('en', 'zh'), true)) {
		$helpFile = DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/{$id}/help/" . ($locale === 'en' ? 'help.php' : "help.{$locale}.php");
		if (is_file($helpFile)) {
			ob_start();
			include $helpFile;
			$help = ob_get_clean();
		} else {
			$help = '<p>Not Found</p>';
		}
	} else {
		$help = '<p>Not Found</p>';
	}
} else {
	$help = "<p>Not Found</p>";
}

$page[ 'body' ] .= "
<script src='/vulnerabilities/help.js'></script>
<link rel='stylesheet' type='text/css' href='/vulnerabilities/help.css' />

<div class=\"body_padded\">
	{$help}
</div>\n";

dvwaHelpHtmlEcho( $page );

?>
