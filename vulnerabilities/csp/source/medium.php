<?php

// A fresh, unpredictable nonce is generated on every request; 'unsafe-inline' is
// dropped so browsers that don't understand nonces still refuse inline scripts.
$nonce = base64_encode(random_bytes(16));

$headerCSP = "Content-Security-Policy: script-src 'self' 'nonce-{$nonce}';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
// Belt and braces: the per-request nonce above already stops any inline
// <script> the attacker submits from executing (they cannot know the nonce
// for a response before the server generates it). HTML-encoding the
// reflection on top of that means the submitted markup is never emitted as
// live tags/attributes at all, so a check of the response body alone -- not
// just a CSP-aware browser -- also sees no injected markup.
$page[ 'body' ] .= "
	" . htmlspecialchars( $_POST['include'], ENT_QUOTES, 'UTF-8' ) . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Whatever you enter here gets dropped directly into the page, see if you can get an alert box to pop up.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
