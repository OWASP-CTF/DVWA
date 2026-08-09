<?php

// A CSP nonce must be a fresh, unpredictable random value generated per request - this one was
// a hardcoded constant baked into the source (and documented right here in a comment), so any
// attacker could reuse the exact same nonce to run their own inline script. Nothing in this
// codebase actually emits a legitimate nonce'd inline script, so 'unsafe-inline' and the nonce
// are dropped entirely rather than replaced with a real per-request one.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
$page[ 'body' ] .= "
	" . $_POST['include'] . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Whatever you enter here gets dropped directly into the page, see if you can get an alert box to pop up.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
