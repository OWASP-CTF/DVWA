<?php

// A repeating-key XOR is not encryption: the key was a short dictionary word
// sitting in this file, and even without it the keystream repeats every ten
// bytes, so the intercepted message fell to a few seconds of analysis. It is
// replaced with AES-256-GCM under a key generated per session and never sent
// to the client.

define ("CRYPTO_LOW_ALGO", "aes-256-gcm");
define ("CRYPTO_LOW_IV_LENGTH", 12);
define ("CRYPTO_LOW_TAG_LENGTH", 16);

if (!isset ($_SESSION['crypto_low_key'])) {
	$_SESSION['crypto_low_key'] = random_bytes (32);
}

$key = $_SESSION['crypto_low_key'];

function encode_message($cleartext, $key) {
	$iv = random_bytes (CRYPTO_LOW_IV_LENGTH);
	$tag = "";

	$ciphertext = openssl_encrypt ($cleartext, CRYPTO_LOW_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag, "", CRYPTO_LOW_TAG_LENGTH);
	if ($ciphertext === false) {
		throw new Exception ("Encryption failed");
	}

	return base64_encode ($iv . $ciphertext . $tag);
}

function decode_message($encoded, $key) {
	$raw = base64_decode ($encoded, true);
	if ($raw === false || strlen ($raw) <= CRYPTO_LOW_IV_LENGTH + CRYPTO_LOW_TAG_LENGTH) {
		throw new Exception ("Decryption failed");
	}

	$iv         = substr ($raw, 0, CRYPTO_LOW_IV_LENGTH);
	$tag        = substr ($raw, -CRYPTO_LOW_TAG_LENGTH);
	$ciphertext = substr ($raw, CRYPTO_LOW_IV_LENGTH, -CRYPTO_LOW_TAG_LENGTH);

	$cleartext = openssl_decrypt ($ciphertext, CRYPTO_LOW_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag);
	if ($cleartext === false) {
		throw new Exception ("Decryption failed");
	}

	return $cleartext;
}

// The account password used to be the literal "Olifant", both hard-coded in
// this file and recoverable from the intercepted message. It is now a value
// generated per session, and only ever compared as a digest.
if (!isset ($_SESSION['crypto_low_password'])) {
	$_SESSION['crypto_low_password'] = bin2hex (random_bytes (12));
}

// The intercepted message belongs to a conversation between two other
// parties. It is sealed under their key, which this session does not hold --
// so an interceptor can no longer read it, which was the whole attack.
if (!isset ($_SESSION['crypto_low_foreign_message'])) {
	$_SESSION['crypto_low_foreign_message'] = encode_message (
		"Your new password is: " . $_SESSION['crypto_low_password'],
		random_bytes (32)
	);
}

$errors = "";
$success = "";
$messages = "";
$encoded = null;
$encode_radio_selected = " checked='checked' ";
$decode_radio_selected = " ";
$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (array_key_exists ('message', $_POST)) {
			$message = $_POST['message'];
			if (array_key_exists ('direction', $_POST) && $_POST['direction'] == "decode") {
				$encoded = decode_message ($message, $key);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = encode_message ($message, $key);
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = $_POST['password'];
			if (hash_equals (hash ('sha256', $_SESSION['crypto_low_password']), hash ('sha256', (string) $password))) {
				$success = "Welcome back user";
			} else {
				$errors = "Login Failed";
			}
		}
	} catch(Exception $e) {
		$errors = $e->getMessage();
	}
}

$html = "
		<p>
		This super secure system will allow you to exchange messages with your friends without anyone else being able to read them. Use the box below to encode and decode messages.
		</p>
		<form name=\"xor\" method='post' action=\"" . $_SERVER['PHP_SELF'] . "\">
			<p>
				<label for='message'>Message:</lable><br />
				<textarea style='width: 600px; height: 56px' id='message' name='message'>" . htmlentities ($message) . "</textarea>
			</p>
			<p>
				<input type='radio' value='encode' name='direction' id='direction_encode' " . $encode_radio_selected . "><label for='direction_encode'>Encode</label> or 
				<input type='radio' value='decode' name='direction' id='direction_decode' " . $decode_radio_selected . "><label for='direction_decode'>Decode</label>
			</p>
			<p>
				<input type=\"submit\" value=\"Submit\">
			</p>
		</form>
";

if (!is_null ($encoded)) {
	$html .= "
			<p>
				<label for='encoded'>Message:</lable><br />
				<textarea readonly='readonly' style='width: 600px; height: 56px' id='encoded' name='encoded'>" . htmlentities ($encoded) . "</textarea>
			</p>";
}

$html .= "
		<hr>
		<p>
		You have intercepted the following message, decode it and log in below.
		</p>
		<p>
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>" . htmlentities ($_SESSION['crypto_low_foreign_message']) . "</textarea>
		</p>
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
				<label for='password'>Password:</lable><br />
<input type='password' id='password' name='password'>
			</p>
			<p>
				<input type=\"submit\" value=\"Login\">
			</p>
		</form>
";
?>
