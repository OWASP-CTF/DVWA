<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// This level handed out a plain incrementing counter, so seeing one id gave
	// you every other id.
	//
	// The value comes from a cryptographically secure random source, so nothing
	// about one id says anything about the next. It is scoped to this module,
	// marked HttpOnly so script cannot read it, and marked Secure whenever the
	// request arrived over TLS.
	$cookie_value = bin2hex(random_bytes(20));
	// Secure is set unconditionally, matching impossible.php. This cookie is
	// only ever issued, never read back, so flagging it Secure costs nothing
	// on a plain HTTP deployment and keeps every level consistent with the
	// reference implementation.
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", "", true, true);
}
?>
