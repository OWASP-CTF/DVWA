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

// A per-session, server-generated secret. It is only known to this server
// and to whoever has actually loaded this page in this session (it gets
// embedded in the hidden "js_secret" field below). The low/medium/high
// "token" formulas below are all public - anyone can read this source and
// reproduce md5(rot13("success")) etc. offline without ever touching the
// app - so on their own they prove nothing. Requiring this nonce as well
// ties a successful submission to a real, live visit to this page, which is
// what an attacker computing the token out-of-band cannot produce.
if ( ! isset( $_SESSION[ 'dvwa_js_secret' ] ) || ! is_string( $_SESSION[ 'dvwa_js_secret' ] ) ) {
	$_SESSION[ 'dvwa_js_secret' ] = bin2hex( random_bytes( 16 ) );
}
$dvwaJsSecret = $_SESSION[ 'dvwa_js_secret' ];

$message = "";
// Check what was sent in to see if it was what was expected
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (array_key_exists ("phrase", $_POST) && array_key_exists ("token", $_POST) && array_key_exists ("js_secret", $_POST)) {

		$phrase = $_POST['phrase'];
		$token = $_POST['token'];
		$submittedSecret = $_POST['js_secret'];

		if ( is_string( $submittedSecret ) && hash_equals( $dvwaJsSecret, $submittedSecret ) ) {
			if ($phrase == "success") {
				switch( dvwaSecurityLevelGet() ) {
					case 'low':
						if ($token == md5(str_rot13("success"))) {
							$message = "<p style='color:red'>Well done!</p>";
						} else {
							$message = "<p>Invalid token.</p>";
						}
						break;
					case 'medium':
						if ($token == strrev("XXsuccessXX")) {
							$message = "<p style='color:red'>Well done!</p>";
						} else {
							$message = "<p>Invalid token.</p>";
						}
						break;
					case 'high':
						if ($token == hash("sha256", hash("sha256", "XX" . strrev("success")) . "ZZ")) {
							$message = "<p style='color:red'>Well done!</p>";
						} else {
							$message = "<p>Invalid token.</p>";
						}
						break;
					default:
						$vulnerabilityFile = 'impossible.php';
						break;
				}
			} else {
				$message = "<p>You got the phrase wrong.</p>";
			}
		} else {
			// Correct phrase/token but no valid proof this request came from
			// a real page load - reuse the same failure message the token
			// check already uses so we don't invent a new page state.
			$message = "<p>Invalid token.</p>";
		}
	} else {
		$message = "<p>Missing phrase or token.</p>";
	}

	// Single-use: rotate the secret so a captured request can't be replayed
	// against the freshly rendered form below.
	$_SESSION[ 'dvwa_js_secret' ] = bin2hex( random_bytes( 16 ) );
	$dvwaJsSecret = $_SESSION[ 'dvwa_js_secret' ];
}

if ( dvwaSecurityLevelGet() == "impossible" ) {
$page[ 'body' ] = <<<EOF
<div class="body_padded">
	<h1>Vulnerability: JavaScript Attacks</h1>

	<div class="vulnerable_code_area">
	<p>
		You can never trust anything that comes from the user or prevent them from messing with it and so there is no impossible level.
	</p>
EOF;
} else {
$page[ 'body' ] = <<<EOF
<div class="body_padded">
	<h1>Vulnerability: JavaScript Attacks</h1>

	<div class="vulnerable_code_area">
	<p>
		Submit the word "success" to win.
	</p>

	$message

	<form name="low_js" method="post">
		<input type="hidden" name="token" value="" id="token" />
		<input type="hidden" name="js_secret" value="$dvwaJsSecret" />
		<label for="phrase">Phrase</label> <input type="text" name="phrase" value="ChangeMe" id="phrase" />
		<input type="submit" id="send" name="send" value="Submit" />
	</form>
EOF;
}

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
