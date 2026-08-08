<?php

define ("KEY", "rainbowclimbinghigh");

# CBC only hides the plaintext, it does not protect it: with a fixed,
# published IV an attacker can flip chosen bits of the first block and turn
# "userid:2" into "userid:1" without ever knowing the key. An AEAD mode
# authenticates the ciphertext, so any tampering is detected on decryption.
define ("ALGO", "aes-256-gcm");
define ("IV_LENGTH", 12);
define ("TAG_LENGTH", 16);

function encrypt ($plaintext, $iv) {
	if (strlen ($iv) != IV_LENGTH) {
		throw new Exception ("IV must be " . IV_LENGTH . " bytes, " . strlen ($iv) . " passed");
	}

	$tag = "";
	$e = openssl_encrypt($plaintext, ALGO, KEY, OPENSSL_RAW_DATA, $iv, $tag, "", TAG_LENGTH);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}

	# The authentication tag travels with the ciphertext.
	return $e . $tag;
}

function decrypt ($ciphertext, $iv) {
	if (strlen ($iv) != IV_LENGTH) {
		throw new Exception ("IV must be " . IV_LENGTH . " bytes, " . strlen ($iv) . " passed");
	}
	if (strlen ($ciphertext) <= TAG_LENGTH) {
		throw new Exception ("Decryption failed");
	}

	$tag  = substr ($ciphertext, -TAG_LENGTH);
	$body = substr ($ciphertext, 0, -TAG_LENGTH);

	$e = openssl_decrypt($body, ALGO, KEY, OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

// Added the debug flag so that when calling from the script
// the function can print the data used to create the token

function create_token ($debug = false) {
	$token = "userid:2";

	# A fresh IV per token, so no two tokens share a keystream.
	$iv = random_bytes (IV_LENGTH);

	if ($debug) {
		print "Clear text token: " . $token . "\n";
		print "Encryption key: " . KEY . "\n";
		print "IV: " . base64_encode ($iv) . "\n";
	}

	$e = encrypt ($token, $iv);
	$data = array (
					"token" => base64_encode ($e),
					"iv" => base64_encode ($iv)
				);
	return json_encode($data);
}

function check_token ($data) {
	$users = array ();
	$users[1] = array ("name" => "Geoffery", "level" => "admin");
	$users[2] = array ("name" => "Bungle", "level" => "user");
	$users[3] = array ("name" => "Zippy", "level" => "user");
	$users[4] = array ("name" => "George", "level" => "user");

	$data_array = false;
	try {
		$data_array = json_decode ($data, true);
	} catch (TypeError $exp) {
		$ret = array (
						"status" => 521,
						"message" => "Data not in JSON format",
						"extra" => $exp->getMessage()
					);
	}

	if (is_null ($data_array)) {
		$ret = array (
						"status" => 522,
						"message" => "Data in wrong format"
					);
	} else {
		if (!array_key_exists ("token", $data_array)) {
			$ret = array (
							"status" => 523,
							"message" => "Missing token"
						);
			return json_encode ($ret);
		}
		if (!array_key_exists ("iv", $data_array)) {
			$ret = array (
							"status" => 524,
							"message" => "Missing IV"
						);
			return json_encode ($ret);
		}
			
		$ciphertext = base64_decode ($data_array['token']);
		$iv = base64_decode ($data_array['iv']);

		# Assume failure
		$ret = array (
						"status" => 500,
						"message" => "Unknown error"
					);
		try {
			$d = decrypt ($ciphertext, $iv); 
			if (preg_match ("/^userid:(\d+)$/", $d, $matches)) {
				$id = $matches[1];
				if (array_key_exists ($id, $users)) {
					$user = $users[$id];
					$ret = array (
									"status" => 200,
									"user" => $user["name"],
									"level" => $user['level']
								);
				} else {
					$ret = array (
									"status" => 525,
									"message" => "User not found"
								);
				}
			} else {
				$ret = array (
								"status" => 527,
								"message" => "No user specified"
							);
			}
		} catch (Exception $exp) {
			$ret = array (
							"status" => 526,
							"message" => "Unable to decrypt token",
							"extra" => $exp->getMessage()
						);
		}
	}
	return json_encode ($ret);
}
