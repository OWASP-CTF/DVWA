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
// The submitted value used to be dropped straight into the page as raw
// markup. The CSP header already blocks any script it contains from
// running, but nothing else did - encode it so it can only ever render as
// inert text, never as HTML.
$page[ 'body' ] .= "
	" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Whatever you enter here gets dropped directly into the page, see if you can get an alert box to pop up.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
