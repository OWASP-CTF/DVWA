<?php

/*
 * This level allowed script-src from pastebin.com, unpkg.com, cdn.jsdelivr.net
 * and friends. Any host that will serve content an attacker uploaded is, for
 * CSP purposes, the attacker's own host. It also dropped whatever URL was
 * posted straight into a <script src>.
 *
 * The policy is now 'self' only, and there is no include field to abuse.
 */
$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'none';";

header($headerCSP);

$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The page loads its script from this origin only. The Content Security Policy no longer lists any third party host, so there is nowhere to point an include at.</p>
	<p>1+2+3+4+5=<span id="answer"></span></p>
	<input type="button" id="solve" value="Solve the sum" />
</form>

<script src="source/impossible.js"></script>
';
