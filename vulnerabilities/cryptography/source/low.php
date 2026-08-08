<?php

function encrypt_this($cleartext, $key) {
	$iv = openssl_random_pseudo_bytes (12);
	$e = openssl_encrypt ($cleartext, 'aes-128-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		return "";
	}
	return $iv . $tag . $e;
}

function decrypt_this($ciphertext, $key) {
	if (strlen ($ciphertext) < 28) {
		return "";
	}
	$e = openssl_decrypt (substr ($ciphertext, 28), 'aes-128-gcm', $key, OPENSSL_RAW_DATA, substr ($ciphertext, 0, 12), substr ($ciphertext, 12, 16));
	if ($e === false) {
		return "";
	}
	return $e;
}

// A key shared with whoever wrote an intercepted message makes this a decryption
// oracle, so the key is generated per session and never leaves the server.
if (!isset ($_SESSION['crypto_key'])) {
	$_SESSION['crypto_key'] = openssl_random_pseudo_bytes (16);
}
$key = $_SESSION['crypto_key'];

// The password is held as a bcrypt hash so the source cannot disclose it.
$password_hash = '$2y$12$ZJ.LDqRmPwS9qZN2Xg0L2e4QhpmO1c7ODesigvJtY7CNg48GhN7Zm';

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
				$encoded = decrypt_this (base64_decode ($message), $key);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = base64_encode(encrypt_this ($message, $key));
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
