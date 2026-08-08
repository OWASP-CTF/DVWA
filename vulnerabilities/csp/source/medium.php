<?php

// A nonce is only worth anything if it is unpredictable and changes every
// response. This one is minted per request, and 'unsafe-inline' is gone, so a
// nonce copied out of an earlier page is of no use.
$csp_nonce = base64_encode( random_bytes( 16 ) );
$headerCSP = "Content-Security-Policy: script-src 'self' 'nonce-{$csp_nonce}';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
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
