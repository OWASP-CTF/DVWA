<?php

/*
 * Key material is never hard coded in the source. It comes from the
 * environment if it has been configured there, otherwise a random key is
 * generated once and kept in a file outside of the web root.
 */

if (!function_exists ('crypto_secret')) {
	function crypto_secret() {
		static $secret = null;

		if ($secret !== null) {
			return $secret;
		}

		$env = getenv ('DVWA_CRYPTO_KEY');
		if ($env !== false && $env !== "") {
			$secret = $env;
			return $secret;
		}

		$key_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dvwa_crypto.key';

		if (is_readable ($key_file)) {
			$stored = @file_get_contents ($key_file);
			if ($stored !== false && $stored !== "") {
				$secret = $stored;
				return $secret;
			}
		}

		$new = base64_encode (random_bytes (32));
		if (@file_put_contents ($key_file, $new) !== false) {
			$secret = $new;
			return $secret;
		}

		// Nowhere to persist a key. Fall back to something stable for this
		// installation rather than changing it on every request.
		$secret = hash ('sha256', php_uname() . '|' . __FILE__ . '|' . @filemtime (__FILE__));
		return $secret;
	}
}

if (!function_exists ('crypto_key')) {
	// A separate key is derived for each purpose so that one part of the
	// application can never be used as an oracle for another.
	function crypto_key ($context) {
		return hash_hkdf ('sha256', crypto_secret(), 32, $context);
	}
}

/*
 * ECB leaks structure and lets blocks be cut and pasted between messages, and
 * any unauthenticated mode lets an attacker tamper with the ciphertext. Use an
 * AEAD mode with a fresh random IV so that every token is both confidential
 * and tamper evident.
 */

function encrypt ($cleartext, $context) {
	$iv = random_bytes (12);
	$tag = "";

	$e = openssl_encrypt ($cleartext, 'aes-256-gcm', crypto_key ($context), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}

	return $iv . $e . $tag;
}

function decrypt ($ciphertext, $context) {
	// 12 byte IV plus a 16 byte authentication tag is the smallest valid token.
	if (strlen ($ciphertext) < 28) {
		throw new Exception ("Decryption failed");
	}

	$iv = substr ($ciphertext, 0, 12);
	$tag = substr ($ciphertext, -16);
	$body = substr ($ciphertext, 12, -16);

	$e = openssl_decrypt ($body, 'aes-256-gcm', crypto_key ($context), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}

	return $e;
}

$key_context = "dvwa/cryptography/medium/tokens";

$errors = "";
$success = "";
$messages = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (!array_key_exists ('token', $_POST) || !is_string ($_POST['token'])) {
			throw new Exception ("No token passed");
		} else {
			$token = trim ($_POST['token']);
			if ($token === "" || strlen ($token) % 2 != 0 || !ctype_xdigit ($token)) {
				throw new Exception ("Token is in wrong format");
			} else {
				$decrypted = decrypt (hex2bin ($token), $key_context);

				$user = json_decode ($decrypted);
				if (!is_object ($user) || !isset ($user->user, $user->ex, $user->level)) {
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

$sooty_plaintext  = '{"user":"sooty",';
$sooty_plaintext .= '"ex":1723620672,';
$sooty_plaintext .= '"level":"admin",';
$sooty_plaintext .= '"bio":"Izzy wizzy let\'s get busy"}';

$sweep_plaintext  = '{"user":"sweep",';
$sweep_plaintext .= '"ex":1723620672,';
$sweep_plaintext .= '"level":"user",';
$sweep_plaintext .= '"bio":"Squeeeeek"}';

$soo_plaintext  = '{"user":"soo",';
$soo_plaintext .= '"ex":1823620672,';
$soo_plaintext .= '"level":"user",';
$soo_plaintext .= '"bio":"I won The Weakest Link"}';

$sooty_token = "";
$sweep_token = "";
$soo_token = "";

try {
	$sooty_token = bin2hex (encrypt ($sooty_plaintext, $key_context));
	$sweep_token = bin2hex (encrypt ($sweep_plaintext, $key_context));
	$soo_token = bin2hex (encrypt ($soo_plaintext, $key_context));
} catch(Exception $e) {
	if ($errors == "") {
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
To ensure your security, we use authenticated encryption (AES-256-GCM with a random IV per token) throughout our application.
</i></blockquote>

		<hr>
		<p>
		Manipulate the session tokens you have captured to log in as Sweep with admin privileges.
";

if ($errors != "") {
	$html .= '<div class="warning">' . htmlentities ($errors) . '</div>';
}

if ($messages != "") {
	$html .= '<div class="nearly">' . htmlentities ($messages) . '</div>';
}

if ($success != "") {
	$html .= '<div class="success">' . htmlentities ($success) . '</div>';
}

$html .= "
		<form name=\"ecb\" method='post' action=\"" . htmlspecialchars ($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">
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
