<?php
// Fixed: Strict CSP headers, removed JSONP vulnerability
// JSONP endpoints are inherently unsafe and should be removed

$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none';";

header($headerCSP);

// Server-side calculation instead of client-side JSONP
function calculateSum() {
	return 1 + 2 + 3 + 4 + 5;
}

$answer = calculateSum();
?>
<?php
if (isset ($_POST['include'])) {
	// Block any script inclusion attempts
	$page[ 'body' ] .= "<!-- Script inclusion blocked for security -->";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Sum calculated server-side for security (1+2+3+4+5=<span id="answer">' . $answer . '</span>)</p>
	<p>JSONP endpoint removed - all calculations now performed server-side.</p>
</form>

<script nonce="' . base64_encode(random_bytes(16)) . '">
// Secure inline script with nonce if needed
console.log("CSP bypass protection enabled");
</script>
';

