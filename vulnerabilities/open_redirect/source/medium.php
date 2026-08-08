<?php

// Open redirect fix: never pass caller-supplied data to header("location: ").
//
// Two accepted forms, both resolved to a server-side target:
//   1. An indirect reference (?redirect=1), the pattern source/impossible.php
//      uses -- the caller names a target by id, never by URL.
//   2. The literal "info.php?id=N" links that ../index.php generates for this
//      level, kept so the module's own navigation still works.
//
// Anything else -- absolute URLs, protocol-relative //host, backslash and
// userinfo tricks, encoded schemes -- matches neither form and is refused
// without ever reaching header().

function open_redirect_resolve_target_medium ($redirect) {
	// Form 1: indirect reference by id.
	if (is_numeric ($redirect)) {
		switch (intval ($redirect)) {
			case 1:
				return "info.php?id=1";
			case 2:
				return "info.php?id=2";
		}
		return "";
	}

	// Form 2: the module's own generated links, id restricted to digits.
	if (preg_match ('/^info\.php\?id=(\d+)$/', $redirect, $matches)) {
		return "info.php?id=" . intval ($matches[1]);
	}

	return "";
}

if (array_key_exists ("redirect", $_REQUEST) && $_REQUEST['redirect'] != "") {
	$target = open_redirect_resolve_target_medium ($_REQUEST['redirect']);

	if ($target != "") {
		header ("location: " . $target);
		exit;
	}
	?>
	Unknown redirect target.
	<?php
	exit;
}

?>
Missing redirect target.
