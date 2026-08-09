<?php

function low_encryption_key () {
	$key = getenv('DVWA_CRYPTO_KEY');
	if ($key === false || strlen($key) < 32) {
		throw new Exception ("Cryptography is not configured");
	}
	return hash('sha256', 'cryptography-low:' . $key, true);
}

function encrypt_message ($plaintext) {
	$nonce = random_bytes(12);
	$ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', low_encryption_key(), OPENSSL_RAW_DATA, $nonce, $tag);
	if ($ciphertext === false) {
		throw new Exception ("Encryption failed");
	}
	return base64_encode($nonce . $tag . $ciphertext);
}

function decrypt_message ($encoded) {
	$data = base64_decode($encoded, true);
	if ($data === false || strlen($data) < 28) {
		throw new Exception ("Message is in the wrong format");
	}
	$nonce = substr($data, 0, 12);
	$tag = substr($data, 12, 16);
	$ciphertext = substr($data, 28);
	$plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', low_encryption_key(), OPENSSL_RAW_DATA, $nonce, $tag);
	if ($plaintext === false) {
		throw new Exception ("Unable to decrypt message");
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
			if (!is_string($message)) {
				throw new Exception ("Message is in the wrong format");
			}
			if (array_key_exists ('direction', $_POST) && $_POST['direction'] == "decode") {
				$encoded = decrypt_message($message);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = encrypt_message($message);
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = $_POST['password'];
			$password_hash = getenv('DVWA_CRYPTO_LOW_PASSWORD_HASH');
			if (!is_string($password) || $password_hash === false || $password_hash === '') {
				throw new Exception ("Login is not configured");
			}
			if (password_verify($password, $password_hash)) {
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
