<?php

// 'unsafe-inline' turns off CSP's inline-script protection entirely, and a
// nonce that is hard-coded and printed into the page (even only as a
// comment, which "View Source" still exposes) is not a secret - anyone can
// read it and put it on their own injected <script nonce="..."> tag.
// Neither gives any real protection, so this uses the same same-origin-only
// policy as the high/impossible levels.
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
