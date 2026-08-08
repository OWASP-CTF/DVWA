<?php

// An allow list of third party script hosts is only ever as trustworthy as the
// least trustworthy host on it, and every host on the original list will serve
// a file a stranger uploaded. The policy is the one the impossible level ships:
// script comes from this origin only, and no inline script runs at all.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
// What was submitted is shown back as text. It is never used to build a script
// element, so the page cannot be talked into fetching and running code of the
// caller's choosing, and it is encoded for the HTML context so it cannot become
// markup either.
$page[ 'body' ] .= "
	" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The Content Security Policy allows script from this site only, and no inline code, so a URL entered here is echoed back rather than included.</p>
	<p>1+2+3+4+5=<span id="answer"></span></p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
	<input type="button" id="solve" value="Solve the sum" />
</form>

<script src="source/impossible.js"></script>
';
