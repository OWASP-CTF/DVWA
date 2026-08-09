<?php

/*
 * Every host on a script-src allow list can run code with the full authority
 * of this origin. Paste sites, code hosting CDNs and JSONP endpoints all let
 * an attacker choose that code, so allow listing them gives away exactly the
 * protection the policy was added for.
 *
 * Only scripts served by this application are trusted. object-src and
 * base-uri are locked down too so the policy cannot be sidestepped with a
 * plugin or a rewritten <base href>.
 */

$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self';";

header($headerCSP);
header("X-Content-Type-Options: nosniff");

?>
<?php

if (isset ($_POST['include'])) {
	// Do not turn a caller-controlled value into a script-fetching primitive,
	// even when the path is same-origin. Reflect it only as text.
	$include = is_string ($_POST['include']) ? $_POST['include'] : "";
	$page[ 'body' ] .= "
	" . htmlspecialchars ($include, ENT_QUOTES, 'UTF-8') . "
";
}

$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The Content Security Policy allows script from this site only. A submitted URL is displayed as text and is never loaded as script.</p>
	<p>1+2+3+4+5=<span id="answer"></span></p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
	<input type="button" id="solve" value="Solve the sum" />
</form>

<script src="source/impossible.js"></script>
';
