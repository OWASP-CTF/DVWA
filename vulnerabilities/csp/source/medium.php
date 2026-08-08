<?php

// A fresh, unpredictable nonce is generated on every request; 'unsafe-inline' is
// dropped so browsers that don't understand nonces still refuse inline scripts.
$nonce = base64_encode(random_bytes(16));

$headerCSP = "Content-Security-Policy: script-src 'self' 'nonce-{$nonce}';";

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
