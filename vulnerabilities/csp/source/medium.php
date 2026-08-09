<?php

$headerCSP = "Content-Security-Policy: script-src 'self';";

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
	<p>Whatever you enter here gets dropped directly into the page, see if you can get an alert box to pop up.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
