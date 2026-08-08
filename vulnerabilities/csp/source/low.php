<?php

$headerCSP = "Content-Security-Policy: script-src 'self';"; // self only: no third-party origins are attacker-usable

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
	// Do not emit a script element for an arbitrary URL and rely on the browser
	// to enforce CSP after the dangerous sink already exists. This page only
	// needs one repository-owned demonstration script, so resolve it through a
	// fixed identifier and reject every other target before rendering markup.
	$allowedScripts = array(
		'sum' => 'source/high.js',
	);
	$scriptId = is_string($_POST['include']) ? $_POST['include'] : '';
	if (array_key_exists($scriptId, $allowedScripts)) {
		$page[ 'body' ] .= "
	<script src='" . htmlspecialchars($allowedScripts[$scriptId], ENT_QUOTES, 'UTF-8') . "'></script>
";
	}
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>You can include scripts from external sources, examine the Content Security Policy and enter a URL to include here:</p>
	<select name="include" id="include">
		<option value="sum">Local sum demonstration</option>
	</select>
	<input type="submit" value="Include" />
</form>
<p>
	You will probably need to do some reading up on what some of the domains allowed by the CSP do and how they can be used.
</p>
';
