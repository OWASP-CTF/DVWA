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

function encrypt_message ($cleartext, $context) {
	// Authenticated encryption with a fresh random IV for every message.
	$iv = random_bytes (12);
	$tag = "";

	$ciphertext = openssl_encrypt ($cleartext, 'aes-256-gcm', crypto_key ($context), OPENSSL_RAW_DATA, $iv, $tag);
	if ($ciphertext === false) {
		throw new Exception ("Unable to encode the message");
	}

	return base64_encode ($iv . $ciphertext . $tag);
}

function decrypt_message ($message, $context) {
	$raw = base64_decode ($message, true);

	// 12 byte IV plus a 16 byte authentication tag is the smallest valid message.
	if ($raw === false || strlen ($raw) < 28) {
		throw new Exception ("Unable to decode the message");
	}

	$iv = substr ($raw, 0, 12);
	$tag = substr ($raw, -16);
	$ciphertext = substr ($raw, 12, -16);

	$cleartext = openssl_decrypt ($ciphertext, 'aes-256-gcm', crypto_key ($context), OPENSSL_RAW_DATA, $iv, $tag);

	// One generic error, never say why the message was rejected.
	if ($cleartext === false) {
		throw new Exception ("Unable to decode the message");
	}

	return $cleartext;
}

$key_context = "dvwa/cryptography/low/messages";

$errors = "";
$success = "";
$messages = "";
$encoded = null;
$encode_radio_selected = " checked='checked' ";
$decode_radio_selected = " ";
$message = "";

/*
 * The account password is stored as a modern password hash, never in clear
 * text, and it is checked with password_verify rather than a == comparison.
 */
if (!array_key_exists ('crypto_low_password_hash', $_SESSION)) {
	$_SESSION['crypto_low_password_hash'] = password_hash (bin2hex (random_bytes (16)), PASSWORD_DEFAULT);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (array_key_exists ('message', $_POST) && is_string ($_POST['message'])) {
			$message = $_POST['message'];
			if (array_key_exists ('direction', $_POST) && $_POST['direction'] == "decode") {
				$encoded = decrypt_message ($message, $key_context);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = encrypt_message ($message, $key_context);
			}
		}
		if (array_key_exists ('password', $_POST) && is_string ($_POST['password'])) {
			$password = $_POST['password'];
			if (password_verify ($password, $_SESSION['crypto_low_password_hash'])) {
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
		<form name=\"xor\" method='post' action=\"" . htmlspecialchars ($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">
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
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>Lg4WGlQZChhSFBYSEB8bBQtPGxdNQSwEHREOAQY=</textarea>
		</p>
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
				<label for='password'>Password:</lable><br />
<input type='password' id='password' name='password'>
			</p>
			<p>
				<input type=\"submit\" value=\"Login\">
			</p>
		</form>
";
?>
