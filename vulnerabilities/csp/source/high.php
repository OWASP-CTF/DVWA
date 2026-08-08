<?php

/*
 * This level dropped whatever was posted straight into the page body. The CSP
 * stopped it running as script, but it still rendered as markup on the DVWA
 * origin, so an <object> or an <iframe> went through untouched.
 *
 * The include field is gone, and the policy now also pins object-src and
 * base-uri, which script-src on its own does not cover.
 */
$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'none';";

header($headerCSP);

$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The page calls ' . DVWA_WEB_PAGE_TO_ROOT . 'vulnerabilities/csp/source/jsonp.php to load the answer. The callback name is fixed server side, so the endpoint cannot be made to emit arbitrary JavaScript from this origin.</p>
	<p>1+2+3+4+5=<span id="answer"></span></p>
	<input type="button" id="solve" value="Solve the sum" />
</form>

<script src="source/high.js"></script>
';
