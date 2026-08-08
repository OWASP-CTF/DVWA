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

# The client side script below is shared by every security level. It never
# writes attacker-controlled data into the page as markup (no document.write
# of concatenated HTML/attribute strings), so it cannot be broken out of with
# a "<script>" tag, a "</option></select>...' injection, or any other markup.
# It also reads the language only from the real query string (location.search)
# and deliberately ignores location.hash, so a payload smuggled after a "#"
# (which never reaches the server) is never even looked at, closing the
# fragment based bypass. This makes the low/medium/high levels behave exactly
# like the impossible level from the client's point of view.
$page[ 'body' ] = <<<'EOF'
<div class="body_padded">
	<h1>Vulnerability: DOM Based Cross Site Scripting (XSS)</h1>

	<div class="vulnerable_code_area">

 		<p>Please choose a language:</p>

		<form name="XSS" method="GET">
			<select name="default">
				<script>
					(function () {
						var selectBox = document.currentScript.parentNode;

						function addOption(value, text, disabled) {
							var option = document.createElement("option");
							// Assigning to the value/text IDL properties never parses
							// the string as HTML, so it cannot inject markup, attributes
							// or new elements - it can only ever become inert text.
							option.value = value;
							option.text = text;
							if (disabled) {
								option.disabled = true;
							}
							selectBox.appendChild(option);
						}

						// Only the actual query string is consulted - the URL fragment
						// ("#...") is never sent to the server and is not read here.
						var params = new URLSearchParams(window.location.search);
						if (params.has("default")) {
							var lang = params.get("default");
							addOption(lang, lang, false);
							addOption("", "----", true);
						}

						addOption("English", "English", false);
						addOption("French", "French", false);
						addOption("Spanish", "Spanish", false);
						addOption("German", "German", false);
					})();
				</script>
			</select>
			<input type="submit" value="Select" />
		</form>
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
