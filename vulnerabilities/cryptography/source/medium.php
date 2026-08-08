<?php
// ECB encrypts every 16-byte block independently and with no integrity
// check, so identical plaintext blocks produce identical ciphertext blocks
// and the blocks of any two captured tokens can be shuffled or spliced at
// will -- which is exactly how a "user" token was turned into an "admin"
// one, without the key. AES-256-GCM binds the whole token together: a fresh
// IV per token kills the block-equality leak, and the authentication tag
// makes any splice or reorder fail outright.

define ("CRYPTO_MED_ALGO", "aes-256-gcm");
define ("CRYPTO_MED_IV_LENGTH", 12);
define ("CRYPTO_MED_TAG_LENGTH", 16);

function encrypt_token ($cleartext, $key) {
	$iv = random_bytes (CRYPTO_MED_IV_LENGTH);
	$tag = "";

	$e = openssl_encrypt($cleartext, CRYPTO_MED_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag, "", CRYPTO_MED_TAG_LENGTH);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}

	return bin2hex ($iv . $e . $tag);
}

function decrypt ($ciphertext, $key) {
	if (strlen ($ciphertext) <= CRYPTO_MED_IV_LENGTH + CRYPTO_MED_TAG_LENGTH) {
		throw new Exception ("Decryption failed");
	}

	$iv   = substr ($ciphertext, 0, CRYPTO_MED_IV_LENGTH);
	$tag  = substr ($ciphertext, -CRYPTO_MED_TAG_LENGTH);
	$body = substr ($ciphertext, CRYPTO_MED_IV_LENGTH, -CRYPTO_MED_TAG_LENGTH);

	$e = openssl_decrypt($body, CRYPTO_MED_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

// The signing key is generated per session rather than being a phrase in the
// source of a page anyone can view.
if (!isset ($_SESSION['crypto_medium_key'])) {
	$_SESSION['crypto_medium_key'] = random_bytes (32);
}

$key = $_SESSION['crypto_medium_key'];

$errors = "";
$success = "";
$messages = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (!array_key_exists ('token', $_POST)) {
			throw new Exception ("No token passed");
		} else {
			$token = $_POST['token'];
			if (!ctype_xdigit ($token) || strlen ($token) % 2 != 0) {
				throw new Exception ("Token is in wrong format");
			} else {
				$decrypted = decrypt(hex2bin ($token), $key);

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
		}
	} catch(Exception $e) {
		$errors = $e->getMessage();
	}
}

// The captured tokens are reissued under the current scheme so the exercise
// still has real material to work with -- they are simply no longer
// splice-able into an admin token.
$captured_sooty = encrypt_token (json_encode (array ("user" => "sooty", "ex" => time() - 86400, "level" => "admin", "bio" => "blah")), $key);
$captured_sweep = encrypt_token (json_encode (array ("user" => "sweep", "ex" => time() - 86400, "level" => "user",  "bio" => "blah")), $key);
$captured_soo   = encrypt_token (json_encode (array ("user" => "soo",   "ex" => time() + 86400, "level" => "user",  "bio" => "blah")), $key);

$html = "
		<p>
		You have managed to get hold of three session tokens for an application you think is using poor cryptography to protect its secrets:
		</p>
		<p>
		<strong>Sooty (admin), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . htmlentities ($captured_sooty) . "</textarea>
		</p>
		<p>
		<strong>Sweep (user), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . htmlentities ($captured_sweep) . "</textarea>
		</p>
		<p>
		<strong>Soo (user), session valid</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . htmlentities ($captured_soo) . "</textarea>
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
