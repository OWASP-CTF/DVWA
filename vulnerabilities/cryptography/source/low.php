<?php

require_once( "crypto_lib.php" );

/*
 * This level used a repeating key XOR with the key "wachtwoord" written into
 * the source. A short repeating key XOR falls to a known plaintext attack in
 * seconds, and a key committed to the repository is not a key at all.
 *
 * Encoding is now AES-256-GCM with a key that comes from the environment.
 */

$errors = "";
$success = "";
$messages = "";
$encoded = null;
$encode_radio_selected = " checked='checked' ";
$decode_radio_selected = " ";
$message = "";

/*
 * The secret the intercepted message carries.
 *
 * It used to be the literal "Olifant", which is the published answer for this
 * module: posting that one word logged you in without decoding anything, so
 * replacing the cipher underneath it changed nothing that mattered. The value
 * is now generated per session, so the only way to produce it is to actually
 * decrypt the message.
 */
if( !isset( $_SESSION[ 'crypto_low_password' ] ) ) {
	$_SESSION[ 'crypto_low_password' ] = bin2hex( random_bytes( 8 ) );
}
$expected_password = $_SESSION[ 'crypto_low_password' ];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (array_key_exists ('message', $_POST)) {
			$message = $_POST['message'];
			if (array_key_exists ('direction', $_POST) && $_POST['direction'] == "decode") {
				$decoded = dvwaCryptoDecrypt ($message);
				if ($decoded === false) {
					$errors = "Could not decode that message: it is not a valid token, or it has been tampered with.";
				} else {
					$encoded = $decoded;
				}
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = dvwaCryptoEncrypt ($message);
			}
		}
		if (array_key_exists ('password', $_POST)) {
			// Constant time comparison, so the check does not leak the answer
			// one character at a time.
			if (hash_equals ($expected_password, (string) $_POST['password'])) {
				$success = "Welcome back user";
			} else {
				$errors = "Login Failed";
			}
		}
	} catch(Exception $e) {
		// Never surface the exception text: it can carry key material and
		// library internals (CWE-209).
		error_log ("cryptography/low: " . $e->getMessage());
		$errors = "Something went wrong processing that message.";
	}
}

// The intercepted message is produced at runtime with the current key, so the
// page stays coherent without a ciphertext baked into the source.
try {
	$intercepted = dvwaCryptoEncrypt ("The password is " . $expected_password);
} catch (Exception $e) {
	error_log ("cryptography/low: " . $e->getMessage());
	$intercepted = "";
}

$html = "
		<p>
		This system lets you exchange messages with your friends. Use the box below to encode and decode messages.
		</p>
		<form name=\"xor\" method='post' action=\"" . htmlspecialchars ($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">
			<p>
				<label for='message'>Message:</label><br />
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
				<label for='encoded'>Result:</label><br />
				<textarea readonly='readonly' style='width: 600px; height: 56px' id='encoded' name='encoded'>" . htmlentities ($encoded) . "</textarea>
			</p>";
}

$html .= "
		<hr>
		<p>
		You have intercepted the following message. Note that this is now authenticated encryption: altering a single byte makes it fail to decode rather than decode into something else.
		</p>
		<p>
		<textarea readonly='readonly' style='width: 600px; height: 56px'>" . htmlentities ($intercepted) . "</textarea>
		</p>
";

if ($errors != "") {
	$html .= '<div class="warning">' . htmlspecialchars ($errors, ENT_QUOTES, 'UTF-8') . '</div>';
}

if ($messages != "") {
	$html .= '<div class="nearly">' . htmlspecialchars ($messages, ENT_QUOTES, 'UTF-8') . '</div>';
}

if ($success != "") {
	$html .= '<div class="success">' . htmlspecialchars ($success, ENT_QUOTES, 'UTF-8') . '</div>';
}

$html .= "
		<form name=\"login\" method='post' action=\"" . htmlspecialchars ($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">
			<p>
				<label for='password'>Password:</label><br />
<input type='password' id='password' name='password'>
			</p>
			<p>
				<input type=\"submit\" value=\"Login\">
			</p>
		</form>
";
?>
