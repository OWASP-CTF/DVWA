<?php

// Several of the domains previously allowed here (pastebin/hastebin, unpkg, cdn.jsdelivr.net)
// are documented above as hosting scripts specifically designed to bypass this CSP - allowing
// arbitrary script execution from any of them defeats the point of restricting script sources
// at all. Only allow scripts from the app's own origin, matching impossible.php.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
$page[ 'body' ] .= "
	<script src='" . $_POST['include'] . "'></script>
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
