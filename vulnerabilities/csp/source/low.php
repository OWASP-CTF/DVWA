<?php

// An allow list of third party script hosts is only ever as trustworthy as the
// least trustworthy host on it, and every host on the original list will serve
// a file a stranger uploaded. Only this origin may serve script, nothing may be
// embedded as an object, and the document base cannot be moved to make a
// relative script path resolve somewhere else.
$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
// Whatever is submitted is shown back as text. It is never used to build a
// script element, so the page cannot be talked into fetching and running code
// of the caller's choosing, and it is encoded for the HTML context so it
// cannot become markup either. This is what the impossible level does.
$page[ 'body' ] .= "
	" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Scripts are only ever loaded from this site. Enter a URL here and it is shown back to you as text rather than included:</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
<p>
	Examine the Content Security Policy and see what it does, and does not, allow.
</p>
';
