<?php

define ("ALGO", "aes-256-gcm");

function encryption_key () {
	$key = getenv('DVWA_CRYPTO_KEY');
	if ($key === false || strlen($key) < 32) {
		throw new Exception ("DVWA_CRYPTO_KEY must contain at least 32 characters");
	}
	return hash('sha256', 'cryptography-impossible:' . $key, true);
}

function encrypt ($plaintext, $iv) {
	# Default padding is PKCS#7 which is interchangeable with PKCS#5
	# https://en.wikipedia.org/wiki/Padding_%28cryptography%29#PKCS#5_and_PKCS#7

	if (strlen ($iv) != 12) {
		throw new Exception ("IV must be 12 bytes, " . strlen ($iv) . " passed");
	}

	$e = openssl_encrypt($plaintext, ALGO, encryption_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}
	return $e . $tag;
}

function decrypt ($ciphertext, $iv) {
	if (strlen ($iv) != 12 || strlen ($ciphertext) < 16) {
		throw new Exception ("Invalid nonce or ciphertext length");
	}

    $tag = substr($ciphertext, -16);
	$text = substr($ciphertext, 0, -16);

	$e = openssl_decrypt($text, ALGO, encryption_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

// Added the debug flag so that when calling from the script
// the function can print the data used to create the token

function create_token () {
	$token = "userid:2";
	$iv = random_bytes(12);

	$e = encrypt ($token, $iv);
	$data = array (
					"token" => base64_encode ($e),
					"iv" => base64_encode ($iv),
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

	if (!is_array ($data_array)) {
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

		if (!is_string ($data_array['token']) || !is_string ($data_array['iv'])) {
			return json_encode (array ("status" => 528, "message" => "Invalid token format"));
		}

		$ciphertext = base64_decode ($data_array['token'], true);
		$iv = base64_decode ($data_array['iv'], true);
		if ($ciphertext === false || $iv === false) {
			return json_encode (array ("status" => 528, "message" => "Invalid token format"));
		}

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
							"message" => "Unable to decrypt token"
						);
		}
	}
	return json_encode ($ret);
}
