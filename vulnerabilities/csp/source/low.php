<?php

$headerCSP = "Content-Security-Policy: script-src 'self';"; // only js hosted on this server may run

header($headerCSP);

?>
<?php
if (isset ($_POST['include']) && is_string ($_POST['include'])) {
$page[ 'body' ] .= "
	" . htmlspecialchars( $_POST['include'], ENT_QUOTES, 'UTF-8' ) . "
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
