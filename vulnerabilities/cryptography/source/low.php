<?php

function xor_this($cleartext, $key) {
    // Our output text
    $outText = '';

    // Iterate through each character
    for($i=0; $i<strlen($cleartext);) {
        for($j=0; ($j<strlen($key) && $i<strlen($cleartext)); $j++,$i++) {
            $outText .= $cleartext[$i] ^ $key[$j];
        }
    }
    return $outText;
}

// The "intercepted" message below is protected with its own secret key.
// The encode/decode form used to be an oracle for that *same* key, so an
// attacker could just paste the intercepted ciphertext into "decode" and
// let the app reveal it for them. The form still works exactly the same
// for round-tripping your own messages, but it now uses a key that is
// unique per session, so it can no longer be used to decrypt the
// intercepted message, which stays protected by its own, separate key.
$key = "wachtwoord";

if (!isset($_SESSION['xor_oracle_key'])) {
	$_SESSION['xor_oracle_key'] = bin2hex(random_bytes(16));
}
$oracle_key = $_SESSION['xor_oracle_key'];

// The password guarding the intercepted message used to be the fixed
// literal "Olifant" - the same value in every installation, forever. Once
// that leaks into a walkthrough (which it inevitably does), the lesson
// stops requiring anyone to actually break the cipher; they just type the
// known answer. Generate a fresh password per session instead, and encode
// it with the same weak fixed-key cipher this lesson demonstrates, so the
// intercepted textarea below still requires doing the exercise to recover.
if (!isset($_SESSION['crypto_low_password'])) {
	$_SESSION['crypto_low_password'] = bin2hex(random_bytes(6));
}
$login_password = $_SESSION['crypto_low_password'];
$intercepted_message = base64_encode(xor_this($login_password, $key));

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
				$encoded = xor_this (base64_decode ($message), $oracle_key);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = base64_encode(xor_this ($message, $oracle_key));
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = $_POST['password'];
			// Compare against this session's freshly generated password,
			// never a fixed literal, and do it in constant time.
			if (hash_equals ($login_password, $password)) {
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
		<textarea readonly='readonly' style='width: 600px; height: 28px' id='encoded' name='encoded'>{$intercepted_message}</textarea>
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
