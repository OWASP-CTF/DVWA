<?php

$nonce = base64_encode(random_bytes(18));
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$nonce}'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'");

if (isset($_POST['include'])) {
	$safeInput = htmlspecialchars((string) $_POST['include'], ENT_QUOTES, 'UTF-8');
	$page['body'] .= "<p>External script inclusion is disabled. Submitted value: {$safeInput}</p>";
}

$page['body'] .= '
<form name="csp" method="POST">
	<p>External script URLs are not accepted. Submitted values are displayed as text.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Submit" />
</form>
<p>1+2+3+4+5=<span id="answer"></span></p>
<input type="button" id="solve" value="Solve the sum" />
<script src="source/impossible.js"></script>
';

?>
