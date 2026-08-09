<?php

define ("ALGO", "aes-256-gcm");

function token_encryption_key () {
	$path = sys_get_temp_dir() . '/dvwa-cryptography-token.key';
	$key = @file_get_contents($path);
	if ($key !== false && strlen($key) === 32) {
		return $key;
	}

	$key = random_bytes(32);
	$handle = @fopen($path, 'x');
	if ($handle !== false) {
		fwrite($handle, $key);
		fclose($handle);
		@chmod($path, 0600);
		return $key;
	}

	$storedKey = @file_get_contents($path);
	return ($storedKey !== false && strlen($storedKey) === 32) ? $storedKey : $key;
}

function encrypt ($plaintext, $iv) {
	# Default padding is PKCS#7 which is interchangeable with PKCS#5
	# https://en.wikipedia.org/wiki/Padding_%28cryptography%29#PKCS#5_and_PKCS#7

	if (strlen ($iv) != 12) {
		throw new Exception ("IV must be 12 bytes, " . strlen ($iv) . " passed");
	}

	$e = openssl_encrypt($plaintext, ALGO, token_encryption_key(), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}
	return $e . $tag;
}

function decrypt ($ciphertext, $iv) {
	if (strlen ($iv) != 12) {
		throw new Exception ("IV must be 12 bytes, " . strlen ($iv) . " passed");
	}

    $tag = substr($ciphertext, -16);
	$text = substr($ciphertext, 0, -16);

	$e = openssl_decrypt($text, ALGO, token_encryption_key(), OPENSSL_RAW_DATA, $iv, $tag);
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
			
		$ciphertext = base64_decode ($data_array['token'], true);
		$iv = base64_decode ($data_array['iv'], true);
		if ($ciphertext === false || $iv === false || strlen($ciphertext) < 17) {
			return json_encode(array('status' => 526, 'message' => 'Invalid token encoding'));
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
							"message" => "Unable to decrypt token",
							"extra" => $exp->getMessage()
						);
		}
	}
	return json_encode ($ret);
}
