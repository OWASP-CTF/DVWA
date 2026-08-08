<?php

$headerCSP = "Content-Security-Policy: script-src 'self';"; // self only: no third-party origins are attacker-usable

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
// Belt and braces: the CSP header above is the primary control (it stops the
// browser executing anything not same-origin), but the reflected value is
// also HTML-encoded so it can never break out of the src='...' attribute to
// inject markup of its own, regardless of what any given client makes of the
// CSP header.
$page[ 'body' ] .= "
	<script src='" . htmlspecialchars( $_POST['include'], ENT_QUOTES, 'UTF-8' ) . "'></script>
";
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
