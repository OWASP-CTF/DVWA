<?php
$page[ 'body' ] .= '<script src="' . DVWA_WEB_PAGE_TO_ROOT . 'vulnerabilities/javascript/source/high.js"></script>';
// The high.js token generator (sha256-based, packed/obfuscated) is a pure,
// static function of the phrase, so it can be fully re-derived offline and
// replayed. Layer the per-session single-use nonce on top of whatever token
// high.js produces, right before the form submits, reusing the sha256()
// function high.js already exposes on window rather than touching the
// packed source.
$page[ 'body' ] .= '
<script>
(function() {
	var sendBtn = document.getElementById("send");
	if (sendBtn) {
		sendBtn.addEventListener("click", function() {
			var nonceEl = document.getElementById("js_nonce");
			var nonce = nonceEl ? nonceEl.value : "";
			var tokenEl = document.getElementById("token");
			tokenEl.value = sha256(tokenEl.value + nonce);
		});
	}
})();
</script>
';
?>
