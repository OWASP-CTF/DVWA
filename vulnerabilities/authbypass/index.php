<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Authorisation Bypass' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'authbypass';
$page[ 'help_button' ]   = 'authbypass';
$page[ 'source_button' ] = 'authbypass';
dvwaDatabaseConnect();

$method            = 'GET';
$vulnerabilityFile = '';
switch( dvwaSecurityLevelGet() ) {
	case 'low':
		$vulnerabilityFile = 'low.php';
		break;
	case 'medium':
		$vulnerabilityFile = 'medium.php';
		break;
	case 'high':
		$vulnerabilityFile = 'high.php';
		break;
	default:
		$vulnerabilityFile = 'impossible.php';
		$method = 'POST';
		break;
}

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/authbypass/source/{$vulnerabilityFile}";

generateSessionToken();
$authToken = htmlspecialchars($_SESSION['session_token'], ENT_QUOTES, 'UTF-8');

$page[ 'body' ] .= '
<div class="body_padded">
	<h1>Vulnerability: Authorisation Bypass</h1>

	<p>This page should only be accessible by the admin user. Your challenge is to gain access to the features using one of the other users, for example <i>gordonb</i> / <i>abc123</i>.</p>

	<div class="vulnerable_code_area">
	<div style="font-weight: bold;color: red;font-size: 120%;" id="save_result"></div>
	<div id="user_form" data-csrf-token="' . $authToken . '"></div>
	<p>
		Welcome to the user manager, please enjoy updating your user\'s details.
	</p>
	';

$page[ 'body' ] .= "
<script src='authbypass.js'></script>

<table id='user_table'>
	<thead>
		<th>ID</th>
		<th>First Name</th>
		<th>Surname</th>
		<th>Update</th>
	</thead>
	<tbody>
	</tbody>
</table>

<script>
	populate_form();
</script>
";

$page[ 'body' ] .= '
		' . 
		$html
		. '
	</div>
</div>';

dvwaHtmlEcho( $page );

?>
