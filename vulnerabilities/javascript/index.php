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

/*
 * Anything worked out by the JavaScript running in the browser is under the
 * control of whoever is driving that browser, so it can never be used to
 * decide whether a request is legitimate. However much the client side code
 * is obfuscated, an attacker can read it, re-implement it, or simply call it
 * from the console.
 *
 * The client-generated token is therefore retained only as a teaching aid.
 * The server does not use it as an authentication or authorization decision.
 * A successful submission requires a fresh anti-CSRF token and
 * re-authentication as the current DVWA user.
 *
 * The phrase is deliberately left as the documented "success": it was never the
 * secret, the page has always printed it, and randomising it would change the
 * module's intended workflow for no gain. All of the security lives in the
 * server-side verification, rather than a bearer value disclosed to the
 * browser.
 */
$_SESSION[ 'javascript_phrase' ] = "success";

$message = "";
// Check what was sent in to see if it was what was expected
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (array_key_exists ("phrase", $_POST) && array_key_exists ("password", $_POST) && array_key_exists ("user_token", $_POST)) {

		$phrase    = is_string( $_POST[ 'phrase' ] ) ? $_POST[ 'phrase' ] : "";
		$password  = is_string( $_POST[ 'password' ] ) ? $_POST[ 'password' ] : "";
		$user_token = is_string( $_POST[ 'user_token' ] ) ? $_POST[ 'user_token' ] : "";

		$session_token = isset( $_SESSION[ 'session_token' ] ) && is_string( $_SESSION[ 'session_token' ] ) ? $_SESSION[ 'session_token' ] : "";
		$valid_csrf = $session_token !== "" && hash_equals( $session_token, $user_token );

		if( !$valid_csrf ) {
			$message = "<p>Invalid request.</p>";
		} elseif( !hash_equals( $_SESSION[ 'javascript_phrase' ], $phrase ) ) {
			$message = "<p>You got the phrase wrong.</p>";
		} else {
			$data = $db->prepare( 'SELECT password FROM users WHERE user = (:user) LIMIT 1;' );
			$current_user = dvwaCurrentUser();
			$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
			$data->execute();
			$stored_password = $data->fetchColumn();

			if( is_string( $stored_password ) && hash_equals( $stored_password, md5( $password ) ) ) {
				$message = "<p style='color:red'>Well done!</p>";
			} else {
				$message = "<p>Invalid credentials.</p>";
			}
		}
	} else {
		$message = "<p>Missing phrase, password, or request token.</p>";
	}
}

// Rotate the anti-CSRF token after every attempt and for the initial form.
generateSessionToken();

$challenge_phrase = htmlspecialchars( $_SESSION[ 'javascript_phrase' ], ENT_QUOTES, 'UTF-8' );
$csrf_field       = tokenField();

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
		Submit the word "$challenge_phrase" to win.
	</p>

	$message

	<form name="low_js" method="post">
		$csrf_field
		<input type="hidden" name="client_token" value="" id="token" />
		<label for="phrase">Phrase</label> <input type="text" name="phrase" value="ChangeMe" id="phrase" />
		<label for="password">Current password</label> <input type="password" name="password" id="password" autocomplete="current-password" />
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
