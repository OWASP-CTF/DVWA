<?php

# Messages are protected with an authenticated cipher (AES-256-GCM) instead of
# a repeating-key XOR. The encoded form is base64 of IV (12) || ciphertext ||
# tag (16). The tag is verified on decode, and the key cannot be recovered by
# feeding chosen plaintext through the encode/decode box.

define ("LOW_ALGO", "aes-256-gcm");
define ("LOW_IV_LENGTH", 12);
define ("LOW_TAG_LENGTH", 16);

# The login password is never stored in clear text, only as a bcrypt hash.
define ("LOW_PASSWORD_HASH", '$2y$10$nX710AFCpNloU6nddiQ4p.WRoIYYpxbrhZS3oQiXpkFrPYS1JNuI2');

function encode_message ($cleartext, $key) {
	$iv  = openssl_random_pseudo_bytes (LOW_IV_LENGTH);
	$tag = "";
	$e   = openssl_encrypt ($cleartext, LOW_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag, "", LOW_TAG_LENGTH);
	if ($e === false) {
		throw new Exception ("Unable to encode the message");
	}
	return base64_encode ($iv . $e . $tag);
}

function decode_message ($encoded, $key) {
	$raw = base64_decode ($encoded, true);
	if ($raw === false || strlen ($raw) < LOW_IV_LENGTH + LOW_TAG_LENGTH) {
		throw new Exception ("Unable to decode the message");
	}

	$iv         = substr ($raw, 0, LOW_IV_LENGTH);
	$tag        = substr ($raw, -LOW_TAG_LENGTH);
	$ciphertext = substr ($raw, LOW_IV_LENGTH, -LOW_TAG_LENGTH);

	$d = openssl_decrypt ($ciphertext, LOW_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag);
	if ($d === false) {
		throw new Exception ("Unable to decode the message");
	}
	return $d;
}

$key = "correcthorsebatterystaple";

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
			$message = is_string ($_POST['message']) ? $_POST['message'] : "";
			if (array_key_exists ('direction', $_POST) && $_POST['direction'] == "decode") {
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
				try {
					$encoded = decode_message (trim ($message), $key);
				} catch (Exception $e) {
					$encoded = null;
					$errors = $e->getMessage();
				}
			} else {
				try {
					$encoded = encode_message ($message, $key);
				} catch (Exception $e) {
					$encoded = null;
					$errors = $e->getMessage();
				}
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = is_string ($_POST['password']) ? $_POST['password'] : "";
			if (password_verify ($password, LOW_PASSWORD_HASH)) {
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
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>Lg4WGlQZChhSFBYSEB8bBQtPGxdNQSwEHREOAQY=</textarea>
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
