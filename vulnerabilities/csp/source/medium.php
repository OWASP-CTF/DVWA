<?php

/*
 * This level allowed 'unsafe-inline' and then published the nonce in the page
 * source. A nonce that is fixed, or that an attacker can read, is not a nonce.
 * Whatever was posted was also written directly into the body.
 *
 * The policy is now 'self' with a per request nonce, and nothing from the
 * request is echoed into the page.
 */
// No nonce is issued: nothing in this page is an inline script, so 'self' is
// the whole policy. A nonce that no element carries is decoration.
$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'none';";

header($headerCSP);

$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The policy allows scripts from this origin only. The previous version published a fixed nonce in the page source alongside unsafe-inline, which is the same as having no policy at all.</p>
	<p>1+2+3+4+5=<span id="answer"></span></p>
	<input type="button" id="solve" value="Solve the sum" />
</form>

<script src="source/impossible.js"></script>
';
