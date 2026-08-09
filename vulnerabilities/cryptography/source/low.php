<?php

// A repeating-key XOR is not encryption, regardless of how the key is
// chosen: submit any message of your own to "Encode" and the response
// hands you a plaintext/ciphertext pair for that same key, which XORs
// together to recover the entire key outright. Randomising the key alone
// (still XOR underneath) does not close that - it only stops this box from
// being reused as a decoder for a *different* secret's ciphertext, it does
// not stop the cipher itself from being broken with a single chosen
// message. The message box below is switched to real authenticated
// encryption (AES-256-GCM) instead, so no amount of querying it ever
// reveals the key. The key is still derived from the same fixed passphrase
// as the original vulnerable version, so the box and the "intercepted"
// message keep sharing one secret and the challenge is solved the same
// way it always was: decode the intercepted message with the box to learn
// the password.
define ('CRYPTOGRAPHY_LOW_PASSPHRASE', 'wachtwoord');

function cryptography_low_key() {
	return hash ('sha256', CRYPTOGRAPHY_LOW_PASSPHRASE, true);
}

function cryptography_low_encode($cleartext) {
	$iv = openssl_random_pseudo_bytes (12);
	$tag = '';
	$ciphertext = openssl_encrypt ($cleartext, 'aes-256-gcm', cryptography_low_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($ciphertext === false) {
		throw new Exception ("Encryption failed");
	}
	// iv + tag + ciphertext, all in one blob so the box only ever hands
	// back (and reads back) a single opaque value.
	return base64_encode ($iv . $tag . $ciphertext);
}

function cryptography_low_decode($encoded) {
	$raw = base64_decode ($encoded, true);
	if ($raw === false || strlen ($raw) < 29) {
		throw new Exception ("Message is in the wrong format");
	}
	$iv = substr ($raw, 0, 12);
	$tag = substr ($raw, 12, 16);
	$ciphertext = substr ($raw, 28);
	// The tag is checked as part of decryption, so a tampered message is
	// rejected outright rather than "decrypted" to attacker-chosen bytes.
	$cleartext = openssl_decrypt ($ciphertext, 'aes-256-gcm', cryptography_low_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($cleartext === false) {
		throw new Exception ("Decryption failed");
	}
	return $cleartext;
}

// The real credential is never kept - or compared - as cleartext, so simply
// reading this source file (the "View Source" button every level exposes)
// does not hand the password to a visitor the way a literal
// `$password == "Olifant"` comparison would have.
$password_hash = '$2y$10$pVrkeS1nutxj67zUvMCpSeinVTqoX5B/MqltraO4uJJwM8IaTGhky';

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
				$encoded = cryptography_low_decode ($message);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = cryptography_low_encode ($message);
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = $_POST['password'];
			if (password_verify ($password, $password_hash)) {
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
