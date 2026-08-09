<?php

define ("CRYPTO_MEDIUM_ALGO", "aes-256-gcm");

// ECB leaks structure and lets whole blocks be swapped between tokens, which is
// how a user token gets turned into an admin one. Tokens use authenticated
// encryption instead, carrying their own IV and authentication tag.

function crypto_key ($key) {
	return hash ("sha256", $key, true);
}

function decrypt ($raw, $key) {
	if (strlen ($raw) < 29) {
		throw new Exception ("Token is in wrong format");
	}
	$iv = substr ($raw, 0, 12);
	$tag = substr ($raw, -16);
	$ciphertext = substr ($raw, 12, -16);

	$e = openssl_decrypt($ciphertext, CRYPTO_MEDIUM_ALGO, crypto_key ($key), OPENSSL_RAW_DATA, $iv, $tag);
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
			$token = trim ($_POST['token']);
			if (strlen($token) % 2 != 0 || !ctype_xdigit($token)) {
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

$html = "
		<p>
		You have managed to get hold of three session tokens for an application you think is using poor cryptography to protect its secrets:
		</p>
		<p>
		<strong>Sooty (admin), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>3c151a5aa15fb746f71c1739f705252e7bf01b007c729c75739902a74c58fa48fc7dde4810aa0e90fdf6d71436aace721dbea67076f57f80f3e9389460432e5d05f737efe317d68690f2d1e97442fb9aed24fa10f296c248c15a20c47e95cbbef88d45</textarea>
		</p>
		<p>
		<strong>Sweep (user), session expired</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>d54d2b9614e1c6d1d97c050220d8f66db56b5d43dcb6b9ec1fc7c70aa493f3ec1b1c76f64c7d5eab29039f5a3e2367571081d1b29f5bfb06414b034d4773dc14c84eade9f96b574896eb1f5c76669e68a6a8c9938f21123d3cea74a50877244436</textarea>
		</p>
		<p>
		<strong>Soo (user), session valid</strong>
		</p>
		<p>
<textarea style='width: 600px; height: 56px'>c57b8276fa5843a54a7309bf06e57062922c600fca22c07f3f85cc4a07aaa7e09eb4896636885a716bb19935829f258dc3117141a542a05f6c9f34fcc7672f76956498e8dc6f549f454cf9598778ac90ee18ff0f1355c46a860cf9580a140a</textarea>
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
To ensure your security, we use aes-256-gcm throughout our application.
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
