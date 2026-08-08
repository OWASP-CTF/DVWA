<?php

// The page's job is to pull in an external script, so the policy still allows
// one, but only from a source that serves fixed library releases. Every host in
// the original list that lets anyone publish arbitrary JavaScript (jsDelivr,
// UNPKG, the paste sites, digi.ninja) is gone, and there is no inline script.
$headerCSP = "Content-Security-Policy: script-src 'self' code.jquery.com;";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
$page[ 'body' ] .= "
	<script src='" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "'></script>
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
