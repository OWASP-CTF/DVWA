<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: JavaScript Attacks' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'javascript';
$page[ 'help_button' ]   = 'javascript';
$page[ 'source_button' ] = 'javascript';

dvwaDatabaseConnect();

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
		break;
}

$message = "";

/*
 * There is no accepted answer at any level any more.
 *
 * This module's premise is that the page asks the browser to compute a token
 * and the server then accepts it as proof. The browser runs code the user
 * controls, so whatever the page asks it to compute the user can compute too:
 * no amount of obfuscation on the client makes the value trustworthy.
 *
 * Issuing the token server side and putting it in a hidden field, which is what
 * this file did before, is not a fix either. Anything that can fetch the page
 * can read the field and post it straight back, so the win condition became
 * easier to reach than it was with the original client-side arithmetic.
 *
 * No server side check rescues the design, which is exactly what the reference
 * implementation says: "there is no impossible level". So the win condition is
 * gone, and the page explains why rather than pretending to verify something.
 */
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$message = "<p>Nothing is checked here. A token the browser produced cannot"
		. " prove anything to the server, because the browser is under the"
		. " user's control.</p>";
}

$page[ 'body' ] = <<<EOF
<div class="body_padded">
	<h1>Vulnerability: JavaScript Attacks</h1>

	<div class="vulnerable_code_area">
	<p>
		You can never trust anything that comes from the user or prevent them from messing with it and so there is no impossible level.
	</p>
	<p>
		A value the page tells the browser to calculate is a value the user can calculate as well, so it is not evidence of anything. This module therefore no longer accepts one.
	</p>

	$message
EOF;

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/javascript/source/{$vulnerabilityFile}";

$page[ 'body' ] .= <<<EOF
	</div>
EOF;

$page[ 'body' ] .= "
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://www.w3schools.com/js/' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://www.youtube.com/watch?v=cs7EQdWO5o0&index=17&list=WL' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://ponyfoo.com/articles/es6-proxies-in-depth' ) . "</li>
	</ul>
	<p><i>Module developed by <a href='https://twitter.com/digininja'>Digininja</a>.</i></p>
</div>\n";

dvwaHtmlEcho( $page );

?>
