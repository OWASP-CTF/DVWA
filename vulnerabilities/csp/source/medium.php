<?php

/*
 * A nonce is only worth anything if an attacker cannot predict it, so it has
 * to be generated with a cryptographically secure random source and it has to
 * change on every response. The old policy shipped a fixed, published nonce
 * and then added 'unsafe-inline' on top of it, which let any injected script
 * run.
 *
 * The nonce is base64 so it can never introduce a CR, LF or ; into the header
 * and rewrite the policy (CWE-113).
 */

$nonce = base64_encode (random_bytes (16));

$headerCSP = "Content-Security-Policy: script-src 'self' 'nonce-" . $nonce . "'; object-src 'none'; base-uri 'self'; frame-ancestors 'self';";

header($headerCSP);

header("X-Content-Type-Options: nosniff");

?>
<?php

/*
 * Defence in depth. The policy stops injected script running, and encoding
 * the value on the way out stops the markup being injected at all.
 */
if (isset ($_POST['include'])) {
	$include = is_string ($_POST['include']) ? $_POST['include'] : "";
	$page[ 'body' ] .= "
	" . htmlspecialchars ($include, ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Whatever you enter here gets encoded and dropped into the page, see if you can get an alert box to pop up.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
