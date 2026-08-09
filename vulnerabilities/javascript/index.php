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

// The old checks derived the expected token from the submitted phrase alone,
// using a fixed transform each level's own (obfuscated, but readable)
// JavaScript carried out - a reimplementable rule, not a secret, so an
// attacker never needed a browser to produce a valid token. Instead, mint an
// unguessable token per level here on the server, hand it to the page ready
// filled in, and require the exact same value back. Nothing about the
// phrase's content ever factors into what token is expected, so there is no
// transform left to reverse-engineer.
function dvwaJsChallengeToken( $level ) {
	if ( !isset( $_SESSION[ 'dvwa_js_tokens' ] ) || !is_array( $_SESSION[ 'dvwa_js_tokens' ] ) ) {
		$_SESSION[ 'dvwa_js_tokens' ] = array();
	}
	if ( empty( $_SESSION[ 'dvwa_js_tokens' ][ $level ] ) ) {
		$_SESSION[ 'dvwa_js_tokens' ][ $level ] = bin2hex( random_bytes( 32 ) );
	}
	return $_SESSION[ 'dvwa_js_tokens' ][ $level ];
}

function dvwaJsChallengeTokenRotate( $level ) {
	$_SESSION[ 'dvwa_js_tokens' ][ $level ] = bin2hex( random_bytes( 32 ) );
	return $_SESSION[ 'dvwa_js_tokens' ][ $level ];
}

$level = dvwaSecurityLevelGet();
$pageToken = ( $level == 'impossible' ) ? '' : dvwaJsChallengeToken( $level );

$message = "";
// Check what was sent in to see if it was what was expected
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (array_key_exists ("phrase", $_POST) && array_key_exists ("token", $_POST)) {

		$phrase = $_POST['phrase'];
		$token = $_POST['token'];

		if ($phrase == "success") {
			if ( $level == "impossible" ) {
				$vulnerabilityFile = 'impossible.php';
			} else {
				if (is_string ($token) && hash_equals ($pageToken, $token)) {
					$message = "<p style='color:red'>Well done!</p>";
				} else {
					$message = "<p>Invalid token.</p>";
				}
				// One-shot: whether the attempt succeeded or not, the value
				// just checked is never valid again.
				$pageToken = dvwaJsChallengeTokenRotate( $level );
			}
		} else {
			$message = "<p>You got the phrase wrong.</p>";
		}
	} else {
		$message = "<p>Missing phrase or token.</p>";
	}
}

$pageToken = htmlspecialchars( $pageToken, ENT_QUOTES, 'UTF-8' );

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
		<input type="hidden" name="token" value="{$pageToken}" id="token" />
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
