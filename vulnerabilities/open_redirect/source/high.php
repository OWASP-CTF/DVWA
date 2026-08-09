<?php

/*
 * Looking for the string "info.php" anywhere in the value is not validation,
 * https://evil.example/info.php and //evil.example/#info.php both pass it.
 *
 * The value that arrives in the request is now only used to look up a
 * destination and the Location header is built from values this application
 * controls.
 */

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

	// http:, https:, javascript:, data: and friends.
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
		?>
		<p>You can only redirect to the info page.</p>
		<?php
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
