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

// If we reach this point, the per-level source file above has already
// decided (for medium/high) whether the requested "default" value is
// acceptable - otherwise it would have redirected away already. So this
// is the single, authoritative value for what gets shown, straight from
// PHP's own query-string parser.
//
// The old version had the client-side <script> independently re-derive the
// "default" value by hand-parsing document.location.href (indexOf/substring)
// and injecting it into the page with document.write() string concatenation.
// That was vulnerable two different ways regardless of the server-side
// check above:
//   - document.write() with raw, unescaped HTML lets *any* markup through
//     (not just "<script>" tags), so a substring blacklist on "<script"
//     never actually covered the sink.
//   - hand-parsing the URL for "default=" does not agree with how PHP (or
//     any spec-compliant query-string parser) resolves a *repeated*
//     "default" parameter, so a crafted URL could get one value accepted
//     by the server-side check while a different, malicious value is the
//     one that actually reaches the DOM.
// Passing PHP's own already-validated value into the page (safely encoded
// for a JS string literal) and building the <option> via DOM APIs instead
// of document.write() closes both classes of bypass at once.
$defaultParam = ( array_key_exists( 'default', $_GET ) && !is_null( $_GET[ 'default' ] ) ) ? (string) $_GET[ 'default' ] : null;
$defaultParamJs = json_encode( $defaultParam, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );

$page[ 'body' ] = '
<div class="body_padded">
	<h1>Vulnerability: DOM Based Cross Site Scripting (XSS)</h1>

	<div class="vulnerable_code_area">

 		<p>Please choose a language:</p>

		<form name="XSS" method="GET">
			<select name="default" id="xss_dom_default_select">
				<script>
					(function () {
						var current  = ' . $defaultParamJs . ';
						var select   = document.getElementById("xss_dom_default_select");
						var addOption = function (value, label, disabled) {
							var opt = document.createElement("option");
							opt.value = value;
							opt.text  = label; // .text is set as a plain string, never parsed as HTML
							if (disabled) opt.disabled = true;
							select.appendChild(opt);
						};

						if (current !== null) {
							addOption(current, current, false);
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
	</div>';

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
