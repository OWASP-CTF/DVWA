<?php

$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include']) && is_string($_POST['include'])) {
	$allowedScripts = array( 'source/impossible.js' );
	if (in_array($_POST['include'], $allowedScripts, true)) {
		$page[ 'body' ] .= "
	<script src='" . htmlspecialchars($_POST['include'], ENT_QUOTES, 'UTF-8') . "'></script>
";
	}
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Select an approved local script to include:</p>
	<select name="include" id="include">
		<option value="source/impossible.js">Sum helper</option>
	</select>
	<input type="submit" value="Include" />
</form>
';
