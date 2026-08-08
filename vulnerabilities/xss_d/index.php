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
			<select name="default" id="default"></select>
			<input type="submit" value="Select" />
		</form>
		<script>
			(function () {
				// The option list is built through the DOM instead of
				// document.write(). textContent and the value property never
				// parse their input as markup, so a payload in the query
				// string or in the fragment cannot become an element.
				var allowed = ["English", "French", "Spanish", "German"];
				var select  = document.getElementById("default");

				function addOption(value, label, disabled) {
					var option = document.createElement("option");
					option.value = value;
					option.textContent = label;
					if (disabled) {
						option.disabled = true;
					}
					select.appendChild(option);
				}

				// Read the selection from the query string, and only accept it
				// when it is one of the known languages.
				// decodeURIComponent throws on a malformed escape such as "%",
				// which would abort this function and leave an empty dropdown.
				var match   = /[?&]default=([^&#]*)/.exec(document.location.search);
				var current = "";
				if (match) {
					try {
						current = decodeURIComponent(match[1].replace(/\+/g, " "));
					} catch (e) {
						current = "";
					}
				}

				if (allowed.indexOf(current) !== -1) {
					addOption(current, current, false);
					addOption("", "----", true);
				}

				for (var i = 0; i < allowed.length; i++) {
					addOption(allowed[i], allowed[i], false);
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
