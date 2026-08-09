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

# Never decode the querystring, at any level. Decoding is what turns a
# percent encoded payload back into markup, and the impossible level
# already relies on not doing it.
$decodeURI = "";

$page[ 'body' ] = <<<EOF
<div class="body_padded">
	<h1>Vulnerability: DOM Based Cross Site Scripting (XSS)</h1>

	<div class="vulnerable_code_area">
 
 		<p>Please choose a language:</p>

		<form name="XSS" method="GET">
			<select name="default">
				<script>
					// Encode anything taken from the URL for the HTML context it is
					// written into, so it can never be parsed as markup.
					function xssdEscape(value) {
						return String(value)
							.replace(/&/g, "&amp;")
							.replace(/</g, "&lt;")
							.replace(/>/g, "&gt;")
							.replace(/"/g, "&quot;")
							.replace(/'/g, "&#39;");
					}

					if (document.location.href.indexOf("default=") >= 0) {
						var lang = document.location.href.substring(document.location.href.indexOf("default=")+8);
						var label = lang;
						try {
							label = $decodeURI(lang);
						} catch (e) {
							label = lang;
						}
						document.write("<option value='" + xssdEscape(lang) + "'>" + xssdEscape(label) + "</option>");
						document.write("<option value='' disabled='disabled'>----</option>");
					}
					    
					document.write("<option value='English'>English</option>");
					document.write("<option value='French'>French</option>");
					document.write("<option value='Spanish'>Spanish</option>");
					document.write("<option value='German'>German</option>");
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
