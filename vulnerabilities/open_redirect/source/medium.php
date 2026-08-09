<?php

/*
 * Blocking the strings "http://" and "https://" is not validation. It misses
 * scheme relative URLs (//evil.example), other schemes (javascript:, data:),
 * mixed case and encoded variants, and backslash tricks.
 *
 * The value that arrives in the request is now only used to look up a
 * destination and the Location header is built from values this application
 * controls.
 */

function looks_absolute ($redirect) {
	if (!is_string ($redirect)) {
		return false;
	}

	// Any scheme, a scheme relative URL, or a backslash variant of one.
	return preg_match ('#^[A-Za-z][A-Za-z0-9+.-]*:#', $redirect) === 1
		|| substr ($redirect, 0, 2) === "//"
		|| substr ($redirect, 0, 1) === "\\"
		|| strpos ($redirect, "@") !== false;
}

function redirect_target ($redirect) {
	if (!is_string ($redirect) || $redirect === "") {
		return "";
	}

	// CR, LF and NUL would let extra headers be injected, a backslash is
	// treated as a slash by several browsers and @ hides a host behind
	// user info.
	if (strpbrk ($redirect, "\r\n\t\0\\@") !== false) {
		return "";
	}

	// //evil.example and /somewhere both leave the pages we own.
	if (substr ($redirect, 0, 1) === "/") {
		return "";
	}

	if (preg_match ('#^[A-Za-z][A-Za-z0-9+.-]*:#', $redirect)) {
		return "";
	}

	$parts = parse_url ($redirect);
	if (!is_array ($parts)) {
		return "";
	}

	if (isset ($parts['scheme']) || isset ($parts['host']) || isset ($parts['port']) ||
		isset ($parts['user']) || isset ($parts['pass']) || isset ($parts['fragment'])) {
		return "";
	}

	// Allow list of the pages this handler is allowed to send people to.
	if (!isset ($parts['path']) || $parts['path'] !== "info.php") {
		return "";
	}

	if (!isset ($parts['query'])) {
		return "info.php";
	}

	$query = array ();
	parse_str ($parts['query'], $query);
	if (count ($query) !== 1 || !isset ($query['id']) || !is_string ($query['id']) || !ctype_digit ($query['id'])) {
		return "";
	}

	// Build the destination from our own values, not from the request.
	return "info.php?id=" . intval ($query['id']);
}

if (array_key_exists ("redirect", $_GET) && is_string ($_GET['redirect']) && $_GET['redirect'] != "") {
	$target = redirect_target ($_GET['redirect']);

	if ($target === "") {
		if (looks_absolute ($_GET['redirect'])) {
			?>
			<p>Absolute URLs not allowed.</p>
			<?php
		} else {
			?>
			<p>Invalid redirect target.</p>
			<?php
		}
		exit;
	}

	header ("Location: " . $target, true, 302);
	exit;
}

?>
<p>Missing redirect target.</p>
<?php
exit;
?>
