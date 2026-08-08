<?php

require_once( "crypto_lib.php" );

/*
 * This level used aes-128-ecb. ECB encrypts each block independently, so equal
 * plaintext blocks give equal ciphertext blocks and an attacker can cut and
 * paste blocks between tokens to build a session that was never issued. There
 * was also no integrity check, so nothing noticed.
 *
 * AES-256-GCM fixes both: the mode is chained, and the authentication tag
 * covers the whole token.
 */

$errors = "";
$success = "";
$messages = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (!array_key_exists ('token', $_POST)) {
			throw new Exception ("No token passed");
		}

		$decrypted = dvwaCryptoDecrypt ($_POST['token']);
		if ($decrypted === false) {
			throw new Exception ("Token failed its integrity check");
		}

		$user = json_decode ($decrypted);
		if ($user === null || !isset ($user->user) || !isset ($user->ex) || !isset ($user->level)) {
			throw new Exception ("Could not decode the token contents");
		}

		if ($user->user == "sweep" && $user->ex > time() && $user->level == "admin") {
			$success = "Welcome administrator Sweep";
		} else {
			$messages = "Login successful but not as the right user.";
		}
	} catch(Exception $e) {
		// Same policy as the low level: the exception text is logged, not
		// rendered (CWE-209).
		error_log( "cryptography/medium: " . $e->getMessage() );
		$errors = "That token could not be used.";
	}
}

// The sample tokens are produced at runtime with the current key.
function crypto_medium_token ($user, $level, $expires) {
	try {
		return dvwaCryptoEncrypt (json_encode (array (
			"user"  => $user,
			"ex"    => $expires,
			"level" => $level,
			"bio"   => "blah",
		)));
	} catch (Exception $e) {
		error_log ("cryptography/medium: " . $e->getMessage());
		return "";
	}
}

$expired = time() - 3600;
$valid   = time() + 3600;

$token_sooty = crypto_medium_token ("sooty", "admin", $expired);
$token_sweep = crypto_medium_token ("sweep", "user", $expired);
$token_soo   = crypto_medium_token ("soo", "user", $valid);

$html = "
		<p>
		You have three session tokens for an application:
		</p>
		<p>
		<strong>Sooty (admin), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px' readonly='readonly'>" . htmlentities ($token_sooty) . "</textarea>
		</p>
		<p>
		<strong>Sweep (user), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px' readonly='readonly'>" . htmlentities ($token_sweep) . "</textarea>
		</p>
		<p>
		<strong>Soo (user), session valid</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px' readonly='readonly'>" . htmlentities ($token_soo) . "</textarea>
		</p>
		<p>
		The token format is:
		</p>
		<pre><code>{
    \"user\": \"example\",
    \"ex\": 1723620372,
    \"level\": \"user\",
    \"bio\": \"blah\"
}</code></pre>
<p>
The application now uses aes-256-gcm. The authentication tag covers the whole token, so cutting blocks out of one token and pasting them into another no longer produces something the server will accept.
</p>

		<hr>
		<p>
		Try to manipulate the captured tokens to log in as Sweep with admin privileges.
		</p>
";

if ($errors != "") {
	$html .= '<div class="warning">' . htmlspecialchars ($errors, ENT_QUOTES, 'UTF-8') . '</div>';
}

if ($messages != "") {
	$html .= '<div class="nearly">' . htmlspecialchars ($messages, ENT_QUOTES, 'UTF-8') . '</div>';
}

if ($success != "") {
	$html .= '<div class="success">' . htmlspecialchars ($success, ENT_QUOTES, 'UTF-8') . '</div>';
}

$html .= "
		<form name=\"gcm\" method='post' action=\"" . htmlspecialchars ($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">
			<p>
				<label for='token'>Token:</label><br />
<textarea style='width: 600px; height: 56px' id='token' name='token'></textarea>
			</p>
			<p>
				<input type=\"submit\" value=\"Submit\">
			</p>
		</form>
";
?>
