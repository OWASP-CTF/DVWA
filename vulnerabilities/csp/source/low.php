<?php

// A curated allow-list is only as safe as its least-trustworthy entry, and
// several of these third-party hosts are themselves JSONP-style endpoints or
// open file-hosting services an attacker can point at to serve arbitrary
// script. Restricting to this origin only, with no third-party or inline
// script, removes that entire class of bypass.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include']) && is_string ($_POST['include'])) {
// The submitted value used to be dropped straight into a <script src="...">
// element, so pointing it at anything same-origin (including this module's
// own jsonp.php) made the browser fetch and run it - CSP's "script-src
// 'self'" does nothing to stop that, since a same-origin script is exactly
// what it's designed to allow. Echoing the value back as escaped text
// instead means there is no longer any way for what's typed here to become
// a live element on the page.
$page[ 'body' ] .= "
	" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The Content Security Policy now restricts script to this origin only, and no inline script runs at all - a URL entered here is shown back as text rather than loaded.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
