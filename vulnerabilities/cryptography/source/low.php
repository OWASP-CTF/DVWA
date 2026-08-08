<?php

require_once __DIR__ . '/crypto_key.php';

define ('CRYPTOGRAPHY_LOW_ALGO', 'aes-256-gcm');
define ('CRYPTOGRAPHY_LOW_NONCE_LEN', 12);
define ('CRYPTOGRAPHY_LOW_TAG_LEN', 16);

// Real authenticated encryption with a per-install random key and a
// fresh nonce on every call, replacing the old repeating-key XOR
// "encoding" which let anyone recover the static key from a single
// chosen-plaintext round trip and then decrypt any other message
// protected under that same key.
function low_encode ($plaintext) {
	$key = dvwa_crypto_get_key ('low', 32);
	$nonce = random_bytes (CRYPTOGRAPHY_LOW_NONCE_LEN);
	$tag = '';
	$ciphertext = openssl_encrypt ($plaintext, CRYPTOGRAPHY_LOW_ALGO, $key, OPENSSL_RAW_DATA, $nonce, $tag);
	if ($ciphertext === false) {
		throw new Exception ("Encoding failed");
	}
	return base64_encode ($nonce . $tag . $ciphertext);
}

function low_decode ($encoded) {
	$key = dvwa_crypto_get_key ('low', 32);
	$raw = base64_decode ((string) $encoded, true);
	$minLen = CRYPTOGRAPHY_LOW_NONCE_LEN + CRYPTOGRAPHY_LOW_TAG_LEN;
	if ($raw === false || strlen ($raw) < $minLen) {
		throw new Exception ("Message is in the wrong format");
	}
	$nonce = substr ($raw, 0, CRYPTOGRAPHY_LOW_NONCE_LEN);
	$tag = substr ($raw, CRYPTOGRAPHY_LOW_NONCE_LEN, CRYPTOGRAPHY_LOW_TAG_LEN);
	$ciphertext = substr ($raw, $minLen);
	$plaintext = openssl_decrypt ($ciphertext, CRYPTOGRAPHY_LOW_ALGO, $key, OPENSSL_RAW_DATA, $nonce, $tag);
	if ($plaintext === false) {
		throw new Exception ("Could not decode message");
	}
	return $plaintext;
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
				$encoded = low_decode ($message);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = low_encode ($message);
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = $_POST['password'];
			if ($password == "Olifant") {
				$success = "Welcome back user";
			} else {
				$errors = "Login Failed";
			}
		}
	} catch(Exception $e) {
		$errors = $e->getMessage();
	}
}

try {
	$intercepted = htmlentities (low_encode ("Your new password is: Olifant"));
} catch (Exception $e) {
	$intercepted = "";
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
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>" . $intercepted . "</textarea>
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
