<?php
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
// The reflected value has no legitimate reason to become markup, and
// leaving it raw means a submitted <script src="..."> pointing anywhere
// same-origin becomes a live element the moment the page renders it - the
// strict CSP above is the thing actually stopping that script from running,
// but there's no reason to also hand out free HTML injection here.
if (isset ($_POST['include']) && is_string ($_POST['include'])) {
$page[ 'body' ] .= "
	" . htmlspecialchars ($_POST['include'], ENT_QUOTES, 'UTF-8') . "
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

