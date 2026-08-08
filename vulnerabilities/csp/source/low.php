<?php

/*
 * Every host on a script-src allow list can run code with the full authority
 * of this origin. Paste sites, code hosting CDNs and JSONP endpoints all let
 * an attacker choose that code, so allow listing them gives away exactly the
 * protection the policy was added for.
 *
 * Only scripts served by this application are trusted. object-src and
 * base-uri are locked down too so the policy cannot be sidestepped with a
 * plugin or a rewritten <base href>.
 */

$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self';";

header($headerCSP);
header("X-Content-Type-Options: nosniff");

?>
<?php

/*
 * The value is dropped into a src attribute, so it is checked against what a
 * local script path can look like and then encoded. That stops the attribute
 * being broken out of and stops absolute, scheme relative and javascript:
 * URLs being requested in the first place.
 */
function is_local_script_path ($path) {
	if (!is_string ($path) || $path === "" || strlen ($path) > 255) {
		return false;
	}

	// No absolute, scheme relative or backslash prefixed URLs and no scheme.
	if (substr ($path, 0, 1) === "/" || substr ($path, 0, 1) === "\\") {
		return false;
	}
	if (preg_match ('#^[A-Za-z][A-Za-z0-9+.-]*:#', $path)) {
		return false;
	}

	// A plain relative path to a script on this server, no directory
	// traversal and no query string.
	if (!preg_match ('#^[A-Za-z0-9_./-]+\.js$#', $path)) {
		return false;
	}
	if (strpos ($path, "..") !== false) {
		return false;
	}

	return true;
}

$include_error = "";

if (isset ($_POST['include'])) {
	$include = is_string ($_POST['include']) ? trim ($_POST['include']) : "";

	if ($include === "") {
		$include_error = "No script given.";
	} elseif (is_local_script_path ($include)) {
		$page[ 'body' ] .= "
	<script src='" . htmlspecialchars ($include, ENT_QUOTES, 'UTF-8') . "'></script>
";
	} else {
		$include_error = "Only relative paths to scripts hosted on this server can be included.";
	}
}

if ($include_error != "") {
	$page[ 'body' ] .= '<div class="warning">' . htmlspecialchars ($include_error, ENT_QUOTES, 'UTF-8') . '</div>';
}

$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>You can include scripts from this server, examine the Content Security Policy and enter a path to include here:</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
<p>
	The policy only trusts scripts served by this application, so scripts hosted anywhere else will not run.
</p>
';
