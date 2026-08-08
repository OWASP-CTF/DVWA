<?php

// A long allowlist of third-party domains (pastebin, hastebin, jsdelivr,
// unpkg, etc) is not actually safe - many of those hosts let anyone publish
// arbitrary script content at an attacker-controlled URL, which completely
// defeats the point of the CSP. Only allow scripts from this origin.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

# These might work if you can't create your own for some reason
# https://cdn.jsdelivr.net/gh/digininja/csp_bypass/alert.js
# https://unpkg.com/@digininja/csp_bypass@1.0.0/index.js

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
