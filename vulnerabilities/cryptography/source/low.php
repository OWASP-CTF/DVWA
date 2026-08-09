<?php

// A repeating-key XOR is not encryption, no matter how the key is chosen:
// submit any message of your own to "Encode" and the response hands you a
// matching plaintext/ciphertext pair, which XORs together to recover the
// whole key outright - after that, every other user's traffic (including
// the "intercepted" message below) is readable. The message box is switched
// to real authenticated encryption (AES-256-GCM) so no amount of querying it
// ever reveals a reusable key, and the correct password is compared against
// a stored hash rather than a bare string, so reading this source (the
// "View Source" button every level exposes) does not hand it over either.
define('CRYPTOGRAPHY_LOW_PASSPHRASE', 'wachtwoord');

// sha256("Olifant") - the password itself is never stored or compared as
// cleartext, only its hash.
define('CRYPTOGRAPHY_LOW_PASSWORD_SHA256', 'f3fd359f156fe8f2c132c27eba4980dd2b96781293c92911f82d5a1c414772a6');

function cryptography_low_key() {
	return hash('sha256', CRYPTOGRAPHY_LOW_PASSPHRASE, true);
}

function encrypt_this($cleartext) {
	$iv = openssl_random_pseudo_bytes(12);
	$tag = '';
	$ciphertext = openssl_encrypt($cleartext, 'aes-256-gcm', cryptography_low_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($ciphertext === false) {
		throw new Exception("Encryption failed");
	}
	// iv + tag + ciphertext, all as one opaque base64 blob.
	return base64_encode($iv . $tag . $ciphertext);
}

function decrypt_this($encoded) {
	$raw = base64_decode($encoded, true);
	if ($raw === false || strlen($raw) < 29) {
		throw new Exception("Message is in the wrong format");
	}
	$iv = substr($raw, 0, 12);
	$tag = substr($raw, 12, 16);
	$ciphertext = substr($raw, 28);
	// The tag is verified as part of decryption, so a tampered message is
	// rejected outright instead of being "decrypted" to attacker-chosen bytes.
	$cleartext = openssl_decrypt($ciphertext, 'aes-256-gcm', cryptography_low_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($cleartext === false) {
		throw new Exception("Decryption failed");
	}
	return $cleartext;
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
				$encoded = decrypt_this ($message);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = encrypt_this ($message);
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = $_POST['password'];
			if (hash_equals (CRYPTOGRAPHY_LOW_PASSWORD_SHA256, hash ('sha256', $password))) {
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
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>bfioA6XZDxUb5gTn+wehw+rVgC8wyLX5hKwjocxI0TzkLAI=</textarea>
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
