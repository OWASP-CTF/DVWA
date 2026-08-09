<?php

// A curated allow-list is only as safe as its least-trustworthy entry - several
// of these third-party hosts are themselves open paste/CDN-style mirrors that
// will happily serve back arbitrary attacker-supplied script. Nothing on this
// page legitimately needs a third-party script source, so this is restricted
// to same-origin only.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include']) && is_string ($_POST['include'])) {
// This used to be dropped straight into a <script src="..."> element, so
// pointing it at anything same-origin made the browser fetch and run it -
// the CSP above does nothing to stop that, since a same-origin script is
// exactly what it is designed to allow. Echoing the value back as escaped
// text instead means there is no way for what is typed here to become a
// live element on the page.
$page[ 'body' ] .= "
	" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The Content Security Policy now restricts script to this origin only, and no inline script runs at all.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
