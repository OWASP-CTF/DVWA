<?php

define ("CRYPTO_LOW_ALGO", "aes-256-gcm");

// A repeating key XOR is not encryption: the key falls straight out of any
// known plaintext. Messages are protected with authenticated encryption
// instead, with a fresh IV for every message.

function crypto_key ($key) {
    return hash ("sha256", $key, true);
}

function encode_message ($cleartext, $key) {
    $iv = openssl_random_pseudo_bytes (12);
    $ciphertext = openssl_encrypt ($cleartext, CRYPTO_LOW_ALGO, crypto_key ($key), OPENSSL_RAW_DATA, $iv, $tag);
    if ($ciphertext === false) {
        throw new Exception ("Encryption failed");
    }
    return base64_encode ($iv . $ciphertext . $tag);
}

function decode_message ($encoded, $key) {
    $raw = base64_decode ($encoded, true);
    if ($raw === false || strlen ($raw) < 28) {
        throw new Exception ("Message is in the wrong format");
    }
    $iv = substr ($raw, 0, 12);
    $tag = substr ($raw, -16);
    $ciphertext = substr ($raw, 12, -16);

    // The tag is verified as part of the decryption, so a modified message is
    // rejected rather than decrypted to something the sender did not write.
    $cleartext = openssl_decrypt ($ciphertext, CRYPTO_LOW_ALGO, crypto_key ($key), OPENSSL_RAW_DATA, $iv, $tag);
    if ($cleartext === false) {
        throw new Exception ("Decryption failed");
    }
    return $cleartext;
}

$key = "wachtwoord";

// Only the hash of the current password is stored, so reading this file does
// not hand over the credential.
define ("CRYPTO_LOW_PASSWORD_HASH", "4b0a0e7edf830414e6fef527d2ea638169803f2a181555b574c7c3a5c583a7f2");

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
			// The old password leaked because the XOR "encryption" that
				// carried it was trivially reversible, so it has been rotated
				// and only its hash is kept here.
				if (hash_equals (CRYPTO_LOW_PASSWORD_HASH, hash ("sha256", $password))) {
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
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>vSTy4HcSVMOPjYiSlkibLIpITmwDnW3CmJR/wF8rda13Wl+qdA50At0fmo5DrQOD/abyUAlJPuK5+k7n</textarea>
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
