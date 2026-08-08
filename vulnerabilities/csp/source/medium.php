<?php

// 'unsafe-inline' plus a static, hardcoded nonce is not a real defense -
// the nonce never changes and is easy to find (it was even committed here
// in a comment), so any injected <script nonce="..."> tag using that same
// value would still execute. Use a strict policy with no inline script
// allowance at all.
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
