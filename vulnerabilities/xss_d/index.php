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

# Never decode the querystring. The browser percent-encodes "<" and ">" when
# it builds location.href, so the fragment arrives as inert text; calling
# decodeURI() on it turned "%3Cscript%3E" back into a live tag for
# document.write() to execute. The fragment never reaches the server, so no
# amount of server-side filtering in low/medium/high could catch it.
$decodeURI = "";

$page[ 'body' ] = <<<EOF
<div class="body_padded">
	<h1>Vulnerability: DOM Based Cross Site Scripting (XSS)</h1>

	<div class="vulnerable_code_area">
 
 		<p>Please choose a language:</p>

		<form name="XSS" method="GET">
			<select name="default" id="default">
				<script>
					// The selected language used to be pasted into a
					// document.write() string. That let the value close the
					// value='...' attribute with a single quote and add an
					// event handler, and -- once decodeURI() had been run
					// over it -- open a <script> tag outright. Neither is
					// possible now: the value is assigned through DOM
					// properties, which never reparse it as markup.
					(function () {
						var select = document.getElementById("default");

						function addOption(value, label, disabled) {
							var option = document.createElement("option");
							option.value = value;
							option.textContent = label;
							if (disabled) {
								option.disabled = true;
							}
							select.appendChild(option);
						}

						var marker = document.location.href.indexOf("default=");
						if (marker >= 0) {
							var lang = document.location.href.substring(marker + 8);
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
