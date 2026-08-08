<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: DOM Based Cross Site Scripting (XSS)' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'xss_d';
$page[ 'help_button' ]   = 'xss_d';
$page[ 'source_button' ] = 'xss_d';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/xss_d/source/{$vulnerabilityFile}";

$page[ 'body' ] = <<<EOF
<div class="body_padded">
	<h1>Vulnerability: DOM Based Cross Site Scripting (XSS)</h1>

	<div class="vulnerable_code_area">

		<p>Please choose a language:</p>

		<form name="XSS" method="GET">
			<select name="default"></select>
			<input type="submit" value="Select" />
		</form>
		<script>
			(function () {
				// The only values that may ever be rendered into this page.
				var allowedLanguages = [ "English", "French", "Spanish", "German" ];
				var langSelect = document.forms["XSS"].elements["default"];

				function addLanguage( value, label, disabled ) {
					var option = document.createElement( "option" );
					option.value = value;
					// textContent (never innerHTML or document.write) means the
					// value is always inserted as text and can never become markup.
					option.textContent = label;
					if ( disabled ) {
						option.disabled = true;
					}
					langSelect.appendChild( option );
				}

				// Read the language from the query string only. The URL fragment is
				// deliberately ignored: it is never sent to the server, so it cannot
				// be validated there and must not be allowed to reach the DOM.
				var requested = new URLSearchParams( window.location.search ).get( "default" );

				if ( requested !== null && allowedLanguages.indexOf( requested ) !== -1 ) {
					addLanguage( requested, requested, false );
					addLanguage( "", "----", true );
				}

				for ( var i = 0; i < allowedLanguages.length; i++ ) {
					addLanguage( allowedLanguages[i], allowedLanguages[i], false );
				}
			})();
		</script>
	</div>
EOF;

$page[ 'body' ] .= "
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/xss/' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/DOM_Based_XSS' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://www.acunetix.com/blog/articles/dom-xss-explained/' ) . "</li>
	</ul>
</div>\n";

dvwaHtmlEcho( $page );

?>
