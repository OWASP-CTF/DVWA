<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ] = 'Help' . $page[ 'title_separator' ].$page[ 'title' ];

$help = "<p>Not Found</p>";
if (array_key_exists ("id", $_GET) &&
	array_key_exists ("security", $_GET) &&
	array_key_exists ("locale", $_GET)) {
	$id       = $_GET[ 'id' ];
	$security = $_GET[ 'security' ];
	$locale   = $_GET[ 'locale' ];

	if (dvwaVulnerabilityNameGet( $id ) !== null &&
		dvwaSecurityLevelIsValid( $security ) &&
		dvwaLocaleIsValid( $locale )) {
		$help_filename = $locale === 'en' ? 'help.php' : "help.{$locale}.php";
		$help_path = DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/{$id}/help/{$help_filename}";

		if (is_file( $help_path )) {
			ob_start();
			include $help_path;
			$help = ob_get_clean();
		}
	}
}

$page[ 'body' ] .= "
<script src='/vulnerabilities/help.js'></script>
<link rel='stylesheet' type='text/css' href='/vulnerabilities/help.css' />

<div class=\"body_padded\">
	{$help}
</div>\n";

dvwaHelpHtmlEcho( $page );

?>
