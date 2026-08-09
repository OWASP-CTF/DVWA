<?php
// Fixed: Strict Content Security Policy with nonce-based script execution
// Removed unsafe external script sources

// Generate a secure nonce for each request
$nonce = base64_encode(random_bytes(16));
$headerCSP = "Content-Security-Policy: script-src 'nonce-" . $nonce . "' 'self'; object-src 'none'; base-uri 'self';";

header($headerCSP);
?>
<?php
// Whitelist of allowed script sources (removed dangerous external sources)
$allowed_sources = ['self'];

if (isset ($_POST['include'])) {
	// Validate against whitelist - only allow self
	$include = $_POST['include'];
	if (in_array($include, $allowed_sources) || $include === 'self') {
		$page[ 'body' ] .= "
	<script nonce='" . $nonce . "' src=''></script>
";
	} else {
		$page[ 'body' ] .= "<!-- External script inclusion blocked by CSP -->";
	}
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>You can include scripts from external sources, examine the Content Security Policy and enter a URL to include here:</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
<p>
	You will probably need to do some reading up on what some of the domains allowed by the CSP do and how they can be used.
</p>
';
