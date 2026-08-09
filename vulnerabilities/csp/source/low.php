<?php

// A long allowlist of third-party domains (pastebin, hastebin, jsdelivr,
// unpkg, etc) is not actually safe - many of those hosts let anyone publish
// arbitrary script content at an attacker-controlled URL, which completely
// defeats the point of the CSP. Only allow scripts from this origin.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

# These might work if you can't create your own for some reason
# https://cdn.jsdelivr.net/gh/digininja/csp_bypass/alert.js
# https://unpkg.com/@digininja/csp_bypass@1.0.0/index.js

?>
<?php
if (isset ($_POST['include'])) {
// The submitted value used to be built into a <script src="..."> tag, so
// the "include a script" feature this page demonstrates was itself the
// gadget: the CSP header restricts *which origin* a script can come from,
// but never stopped this page from being talked into emitting a script
// element pointing wherever the caller chose within that origin. Show
// the value back as inert text instead of ever using it to construct a
// script element.
$page[ 'body' ] .= "
	" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Scripts may only load from this origin, so a URL entered here is shown back as text rather than included.</p>
	<p>1+2+3+4+5=<span id="answer"></span></p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
	<input type="button" id="solve" value="Solve the sum" />
</form>

<script src="source/low.js"></script>
';
