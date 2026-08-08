<?php

// An allow list of third party hosts is only ever as trustworthy as the least
// trustworthy host on it, and any host that lets a stranger publish a file is
// a way straight past the policy. Only this origin may serve script, and no
// inline script runs at all, which is what the impossible level does.
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
$page[ 'body' ] .= "
	<script src='" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "'></script>
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
