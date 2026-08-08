<?php

require_once __DIR__ . '/crypto_key.php';

define ('CRYPTOGRAPHY_MEDIUM_ALGO', 'aes-256-gcm');
define ('CRYPTOGRAPHY_MEDIUM_NONCE_LEN', 12);
define ('CRYPTOGRAPHY_MEDIUM_TAG_LEN', 16);

// Authenticated encryption (random nonce per token, per-install random
// key, integrity-checked on decrypt) replaces AES-128-ECB. ECB encrypts
// every 16-byte block independently with no chaining, so identical
// plaintext blocks always produce identical ciphertext blocks and any
// two blocks encrypted under the same key are interchangeable - that is
// what let an attacker splice a username block from one token, an
// expiry block from another and a privilege-level block from a third
// into one forged, still-"decryptable" token. GCM ties every byte of
// the ciphertext to a single authentication tag, so cutting and pasting
// blocks (or any other bit-flip) makes the whole token fail to decrypt.
function medium_encrypt ($plaintext) {
	$key = dvwa_crypto_get_key ('medium', 32);
	$nonce = random_bytes (CRYPTOGRAPHY_MEDIUM_NONCE_LEN);
	$tag = '';
	$ciphertext = openssl_encrypt ($plaintext, CRYPTOGRAPHY_MEDIUM_ALGO, $key, OPENSSL_RAW_DATA, $nonce, $tag);
	if ($ciphertext === false) {
		throw new Exception ("Encryption failed");
	}
	return bin2hex ($nonce . $tag . $ciphertext);
}

function decrypt ($token, $key) {
	if (!is_string ($token) || $token === '' || strlen ($token) % 2 !== 0 || !ctype_xdigit ($token)) {
		throw new Exception ("Token is in wrong format");
	}
	$raw = hex2bin ($token);
	$minLen = CRYPTOGRAPHY_MEDIUM_NONCE_LEN + CRYPTOGRAPHY_MEDIUM_TAG_LEN;
	if ($raw === false || strlen ($raw) <= $minLen) {
		throw new Exception ("Token is in wrong format");
	}
	$nonce = substr ($raw, 0, CRYPTOGRAPHY_MEDIUM_NONCE_LEN);
	$tag = substr ($raw, CRYPTOGRAPHY_MEDIUM_NONCE_LEN, CRYPTOGRAPHY_MEDIUM_TAG_LEN);
	$ciphertext = substr ($raw, $minLen);
	$e = openssl_decrypt ($ciphertext, CRYPTOGRAPHY_MEDIUM_ALGO, $key, OPENSSL_RAW_DATA, $nonce, $tag);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

$key = dvwa_crypto_get_key ('medium', 32);

$errors = "";
$success = "";
$messages = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (!array_key_exists ('token', $_POST)) {
			throw new Exception ("No token passed");
		} else {
			$token = $_POST['token'];
			$decrypted = decrypt ($token, $key);

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

try {
	$sooty_token = medium_encrypt (json_encode (array (
		"user"  => "sooty",
		"ex"    => time() - 3600,
		"level" => "admin",
		"bio"   => "Izzy wizzy let's get busy"
	)));
	$sweep_token = medium_encrypt (json_encode (array (
		"user"  => "sweep",
		"ex"    => time() - 3600,
		"level" => "user",
		"bio"   => "Squeeeeek"
	)));
	$soo_token = medium_encrypt (json_encode (array (
		"user"  => "soo",
		"ex"    => time() + 3600,
		"level" => "user",
		"bio"   => "I won The Weakest Link"
	)));
} catch (Exception $e) {
	$sooty_token = $sweep_token = $soo_token = "";
}

$html = "
		<p>
		You have managed to get hold of three session tokens for an application you think is using poor cryptography to protect its secrets:
		</p>
		<p>
		<strong>Sooty (admin), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . htmlentities ($sooty_token) . "</textarea>
		</p>
		<p>
		<strong>Sweep (user), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . htmlentities ($sweep_token) . "</textarea>
		</p>
		<p>
		<strong>Soo (user), session valid</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>" . htmlentities ($soo_token) . "</textarea>
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
