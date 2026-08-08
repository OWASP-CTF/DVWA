<?php
# Tokens are protected with an authenticated cipher (AES-256-GCM).
# The wire format is: IV (12 bytes) || ciphertext || tag (16 bytes)
# The tag is verified on decryption, so cut-and-paste / block splicing of
# ciphertext from other tokens is rejected instead of silently decrypting.

define ("MEDIUM_ALGO", "aes-256-gcm");
define ("MEDIUM_IV_LENGTH", 12);
define ("MEDIUM_TAG_LENGTH", 16);

function decrypt ($raw, $key) {
	if (!is_string ($raw) || strlen ($raw) <= MEDIUM_IV_LENGTH + MEDIUM_TAG_LENGTH) {
		throw new Exception ("Token is in wrong format");
	}

	$iv         = substr ($raw, 0, MEDIUM_IV_LENGTH);
	$tag        = substr ($raw, -MEDIUM_TAG_LENGTH);
	$ciphertext = substr ($raw, MEDIUM_IV_LENGTH, -MEDIUM_TAG_LENGTH);

	$e = openssl_decrypt($ciphertext, MEDIUM_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

$key = "ik ben een aardbei";

$errors = "";
$success = "";
$messages = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (!array_key_exists ('token', $_POST)) {
			throw new Exception ("No token passed");
		} else {
			$token = is_string ($_POST['token']) ? trim ($_POST['token']) : "";
			# Expect hex encoding of IV || ciphertext || tag, so at minimum
			# 12 + 1 + 16 bytes = 58 hex characters, and an even length.
			if (strlen ($token) < 58 || strlen ($token) % 2 != 0 || !ctype_xdigit ($token)) {
				throw new Exception ("Token is in wrong format");
			} else {
				$decrypted = decrypt(hex2bin ($token), $key);

				$user = json_decode ($decrypted);
				if ($user === null || !is_object ($user) ||
					!isset ($user->user) || !isset ($user->ex) || !isset ($user->level)) {
					throw new Exception ("Could not decode JSON object.");
				}

				if ($user->user == "sweep" && $user->ex > time() && $user->level == "admin") {
					$success = "Welcome administrator Sweep";
				} else {
					$messages = "Login successful but not as the right user.";
				}
			}
		}
	} catch(Exception $e) {
		$errors = $e->getMessage();
	}
}

$html = "
		<p>
		You have managed to get hold of three session tokens for an application you think is using poor cryptography to protect its secrets:
		</p>
		<p>
		<strong>Sooty (admin), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>e287af752ed3f9601befd45726785bd9b85bb230876912bf3c66e50758b222d0837d1e6b16bfae07b776feb7afe576305aec34b41499579d3fb6acc8dc92fd5fcea8743c3b2904de83944d6b19733cdb48dd16048ed89967c250ab7f00629dba</textarea>
		</p>
		<p>
		<strong>Sweep (user), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>3061837c4f9debaf19d4539bfa0074c1b85bb230876912bf3c66e50758b222d083f2d277d9e5fb9a951e74bee57c77a3caeb574f10f349ed839fbfd223903368873580b2e3e494ace1e9e8035f0e7e07</textarea>
		</p>
		<p>
		<strong>Soo (user), session valid</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>5fec0b1c993f46c8bad8a5c8d9bb9698174d4b2659239bbc50646e14a70becef83f2d277d9e5fb9a951e74bee57c77a3c9acb1f268c06c5e760a9d728e081fab65e83b9f97e65cb7c7c4b8427bd44abc16daa00fd8cd0105c97449185be77ef5</textarea>
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
