<?php
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
// Defense in depth: this value is only ever meant to be shown back to the
// submitter as plain text (there's no legitimate reason for it to become
// markup), so it's HTML-encoded before being appended to the page body.
// This alone isn't the CSP-bypass fix - the strict "script-src 'self'"
// policy above already stops a raw <script>...</script> here from running
// - but leaving user input completely unescaped in the response body is
// its own bad practice and worth closing regardless.
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

