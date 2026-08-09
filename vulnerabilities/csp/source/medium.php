<?php

// 'unsafe-inline' switches CSP's inline-script protection off entirely, and
// a nonce that is hard-coded and printed straight into the page (even just
// in a comment) is not a secret at all - anyone can read it and put it on
// their own injected <script nonce="..."> tag. Neither of those give any
// real protection, so this page uses the same policy as the high/impossible
// levels: only same-origin scripts are allowed, with no inline-script
// exception.
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
