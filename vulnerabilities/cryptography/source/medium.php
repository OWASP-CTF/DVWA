<?php
function medium_encryption_key () {
	$key = getenv('DVWA_CRYPTO_KEY');
	if ($key === false || strlen($key) < 32) {
		throw new Exception ("Cryptography is not configured");
	}
	return hash('sha256', 'cryptography-medium:' . $key, true);
}

function medium_encrypt ($plaintext) {
	$nonce = random_bytes(12);
	$ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', medium_encryption_key(), OPENSSL_RAW_DATA, $nonce, $tag);
	if ($ciphertext === false) {
		throw new Exception ("Encryption failed");
	}
	return $nonce . $tag . $ciphertext;
}

function medium_decrypt ($data) {
	if (strlen($data) < 28) {
		throw new Exception ("Decryption failed");
	}
	$nonce = substr($data, 0, 12);
	$tag = substr($data, 12, 16);
	$ciphertext = substr($data, 28);
	$plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', medium_encryption_key(), OPENSSL_RAW_DATA, $nonce, $tag);
	if ($plaintext === false) {
		throw new Exception ("Decryption failed");
	}
	return $plaintext;
}

$errors = "";
$success = "";
$messages = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (!array_key_exists ('token', $_POST)) {
			throw new Exception ("No token passed");
		} else {
			$token = $_POST['token'];
			if (!is_string($token) || $token === '' || strlen($token) > 4096 || strlen($token) % 2 != 0 || !ctype_xdigit($token)) {
				throw new Exception ("Token is in wrong format");
			} else {
				$decrypted = medium_decrypt(hex2bin ($token));

				$user = json_decode ($decrypted, true);
				if (!is_array($user) || !isset($user['user'], $user['ex'], $user['level']) ||
					!is_string($user['user']) || !is_int($user['ex']) || !is_string($user['level'])) {
					throw new Exception ("Could not decode JSON object.");
				}

				if ($user['user'] === "sweep" && $user['ex'] > time() && $user['level'] === "admin") {
					$success = "Welcome administrator Sweep";
				} else {
					$messages = "Login successful but not as the right user.";
				}
			}
		}
	} catch(Throwable $e) {
		$errors = "Token validation failed";
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
Session tokens use authenticated encryption and are validated before use.
</i></blockquote>

		<hr>
		<p>
		Submit a valid authenticated session token to log in as Sweep with admin privileges.
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
