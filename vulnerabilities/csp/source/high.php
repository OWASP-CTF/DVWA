<?php

/*
 * script-src 'self' is only as strong as the weakest script this origin will
 * serve. A JSONP endpoint that echoes back a caller supplied callback name is
 * an arbitrary script generator sitting on the allowed origin, so the callback
 * is now restricted to an allow list in source/jsonp.php.
 */

$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self';";

header($headerCSP);

?>
<?php

// Defence in depth, the value never reaches the browser as markup.
if (isset ($_POST['include'])) {
	$include = is_string ($_POST['include']) ? $_POST['include'] : "";
	$page[ 'body' ] .= "
	" . htmlspecialchars ($include, ENT_QUOTES, 'UTF-8') . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The page makes a call to ' . DVWA_WEB_PAGE_TO_ROOT . '/vulnerabilities/csp/source/jsonp.php to load some code. Modify that page to run your own code.</p>
	<p>1+2+3+4+5=<span id="answer"></span></p>
	<input type="button" id="solve" value="Solve the sum" />
</form>

<script src="source/high.js"></script>
';
