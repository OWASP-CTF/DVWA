<?php
// Fixed: Strict CSP with secure nonce, removed 'unsafe-inline'
// Generate cryptographically secure nonce for each request

$nonce = base64_encode(random_bytes(16));
$headerCSP = "Content-Security-Policy: script-src 'nonce-" . $nonce . "' 'self'; object-src 'none'; base-uri 'self'; form-action 'self';";

header($headerCSP);

// Re-enable XSS protection header
header("X-XSS-Protection: 1; mode=block");
?>
<?php
// Sanitize user input before including in page
if (isset ($_POST['include'])) {
	// Escape HTML to prevent XSS
	$safe_include = htmlspecialchars($_POST['include'], ENT_QUOTES, 'UTF-8');
	$page[ 'body' ] .= "
	" . $safe_include . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Whatever you enter here gets dropped directly into the page, see if you can get an alert box to pop up.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
