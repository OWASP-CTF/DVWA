<?php

// The previous allowlist included hosts that will happily serve back
// arbitrary, attacker-supplied JavaScript to anyone (public paste/CDN-style
// mirrors such as pastebin/hastebin, and package mirrors like unpkg.com /
// cdn.jsdelivr.net that will serve any package an attacker chooses to
// publish). Allowing script-src from a domain is only as safe as that
// domain's promise never to host attacker-chosen content, so an allowlist
// should only ever contain 'self' plus origins that genuinely can't be used
// that way. Nothing on this page legitimately needs a third-party script
// source, so script-src is restricted to 'self'.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
$page[ 'body' ] .= "
	<script src='" . $_POST['include'] . "'></script>
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>You can include scripts from external sources, examine the Content Security Policy and enter a URL to include here:</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
<p>
	You will probably need to do some reading up on what some of the domains allowed by the CSP do and how they can be used.
</p>
';
