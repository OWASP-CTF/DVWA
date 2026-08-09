<?php
// ECB mode encrypts each 16-byte block independently, so identical
// plaintext blocks always produce identical ciphertext blocks. That lets an
// attacker cut-and-paste blocks between different captured tokens (e.g.
// combining the "admin" block from one token with the "sweep" block from
// another) to forge a new, validly-decryptable token without ever knowing
// the key. Switching cipher mode alone is not the fix here: the three
// example tokens below are fixed legacy ciphertext this lab has always
// shipped, so re-keying them under a different mode would just make them
// stop decrypting at all. What actually needs to change is that nothing
// should be decrypted, spliced or not, without first proving the ciphertext
// hasn't been touched - a MAC computed over the whole ciphertext, with a key
// the token holder never sees, does that: any byte changed anywhere in the
// ciphertext - including a spliced-in block from a different token - makes
// the tag fail to verify, so decryption is refused before any bytes of the
// tampered token are ever produced.
function decrypt ($ciphertext, $key) {
	$e = openssl_decrypt($ciphertext, 'aes-128-ecb', $key, OPENSSL_PKCS1_PADDING);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

function verify_and_decrypt ($token, $key, $mac_key) {
	$mac_hex_len = 64; // hex-encoded SHA-256 tag

	if (!is_string ($token) || strlen ($token) <= $mac_hex_len || (strlen ($token) - $mac_hex_len) % 32 != 0) {
		throw new Exception ("Token is in wrong format");
	}

	$ciphertext_hex = substr ($token, 0, -$mac_hex_len);
	$provided_mac   = substr ($token, -$mac_hex_len);
	$ciphertext     = hex2bin ($ciphertext_hex);

	if ($ciphertext === false || !ctype_xdigit ($provided_mac)) {
		throw new Exception ("Token is in wrong format");
	}

	$expected_mac = hash_hmac ('sha256', $ciphertext, $mac_key);

	// Constant-time comparison: a timing side-channel on tag verification
	// would let an attacker forge a valid tag one byte at a time.
	if (!hash_equals ($expected_mac, $provided_mac)) {
		throw new Exception ("Token authentication failed");
	}

	return decrypt ($ciphertext, $key);
}

$key = "ik ben een aardbei";
// Kept only on the server, appended to every token issued below, and
// verified on every token submitted back. Never sent to the caller, so
// splicing ciphertext bytes - which is all an attacker who intercepts a
// token can do - has no way to produce a tag that still verifies.
$mac_key = "1f3e9b7c-medium-token-authentication-key";

$errors = "";
$success = "";
$messages = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (!array_key_exists ('token', $_POST)) {
			throw new Exception ("No token passed");
		} else {
			$decrypted = verify_and_decrypt ($_POST['token'], $key, $mac_key);

			$user = json_decode ($decrypted);
			if ($user === null) {
				throw new Exception ("Could not decode JSON object.");
			}

			if ($user->user == "sweep" && $user->ex > time() && $user->level == "admin") {
				$success = "Welcome administrator Sweep";
			} else {
				$messages = "Login successful but not as the right user.";
			}
		}
	} catch(Exception $e) {
		$errors = $e->getMessage();
	}
}

// The three tokens below are the fixed example ciphertexts this lab has
// always shipped; each is now suffixed with the hex-encoded HMAC tag that
// $mac_key produces for it, so submitting one back unmodified still decrypts
// exactly as before. Splicing blocks between them - the intended attack -
// changes the ciphertext without the corresponding tag, so it is rejected by
// verify_and_decrypt() rather than silently decrypting to attacker-chosen
// bytes.
function mac_token ($ciphertext_hex, $mac_key) {
	return $ciphertext_hex . hash_hmac ('sha256', hex2bin ($ciphertext_hex), $mac_key);
}

$sooty_token = mac_token ("e287af752ed3f9601befd45726785bd9b85bb230876912bf3c66e50758b222d0837d1e6b16bfae07b776feb7afe576305aec34b41499579d3fb6acc8dc92fd5fcea8743c3b2904de83944d6b19733cdb48dd16048ed89967c250ab7f00629dba", $mac_key);
$sweep_token = mac_token ("3061837c4f9debaf19d4539bfa0074c1b85bb230876912bf3c66e50758b222d083f2d277d9e5fb9a951e74bee57c77a3caeb574f10f349ed839fbfd223903368873580b2e3e494ace1e9e8035f0e7e07", $mac_key);
$soo_token   = mac_token ("5fec0b1c993f46c8bad8a5c8d9bb9698174d4b2659239bbc50646e14a70becef83f2d277d9e5fb9a951e74bee57c77a3c9acb1f268c06c5e760a9d728e081fab65e83b9f97e65cb7c7c4b8427bd44abc16daa00fd8cd0105c97449185be77ef5", $mac_key);

$html = "
		<p>
		You have managed to get hold of three session tokens for an application you think is using poor cryptography to protect its secrets:
		</p>
		<p>
		<strong>Sooty (admin), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . $sooty_token . "</textarea>
		</p>
		<p>
		<strong>Sweep (user), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . $sweep_token . "</textarea>
		</p>
		<p>
		<strong>Soo (user), session valid</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . $soo_token . "</textarea>
		</p>
		<p>
		Based on the documentation, you know the format of the token is:
		</p>
		<pre><code>{
    \"user\": \"example\",
    \"ex\": 1723620372,
    \"level\": \"user\",
    \"bio\": \"blah\"
}</code></pre>
<p>
You also spot this comment in the docs:
</p>
<blockquote><i>
To ensure your security, we use aes-128-ecb throughout our application.
</i></blockquote>

		<hr>
		<p>
		Manipulate the session tokens you have captured to log in as Sweep with admin privileges.
";

if ($errors != "") {
	$html .= '<div class="warning">' . $errors . '</div>';
}

if ($messages != "") {
	$html .= '<div class="nearly">' . $messages . '</div>';
}

if ($success != "") {
	$html .= '<div class="success">' . $success . '</div>';
}

$html .= "
		<form name=\"ecb\" method='post' action=\"" . $_SERVER['PHP_SELF'] . "\">
			<p>
				<label for='token'>Token:</lable><br />
<textarea style='width: 600px; height: 56px' id='token' name='token'></textarea>
			</p>
			<p>
				<input type=\"submit\" value=\"Submit\">
			</p>
		</form>
";
?>
