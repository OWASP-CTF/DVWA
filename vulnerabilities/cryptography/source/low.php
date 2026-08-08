<?php

function encrypt_message($cleartext, $key) {
	$nonce = random_bytes(12);
	$ciphertext = openssl_encrypt($cleartext, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $nonce, $tag);
	if ($ciphertext === false) {
		throw new Exception("Encryption failed");
	}

	return base64_encode($nonce . $tag . $ciphertext);
}

function decrypt_message($message, $key) {
	$payload = base64_decode($message, true);
	if ($payload === false || strlen($payload) < 28) {
		throw new Exception("Message is in the wrong format");
	}

	$nonce = substr($payload, 0, 12);
	$tag = substr($payload, 12, 16);
	$ciphertext = substr($payload, 28);
	$cleartext = openssl_decrypt($ciphertext, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $nonce, $tag);
	if ($cleartext === false) {
		throw new Exception("Decryption failed");
	}

	return $cleartext;
}

if (!isset($_SESSION['cryptography_low_key'])) {
	$_SESSION['cryptography_low_key'] = random_bytes(32);
}

$key = $_SESSION['cryptography_low_key'];
$intercepted_message = encrypt_message("Your new password is: Olifant", $key);

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
				throw new Exception("Message is in the wrong format");
			}
			if (array_key_exists ('direction', $_POST) && $_POST['direction'] == "decode") {
				$encoded = decrypt_message($message, $key);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = encrypt_message($message, $key);
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = $_POST['password'];
			if (is_string($password) && hash_equals("Olifant", $password)) {
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
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>" . htmlentities ($intercepted_message) . "</textarea>
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
